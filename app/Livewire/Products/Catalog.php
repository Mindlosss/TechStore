<?php

namespace App\Livewire\Products;

use App\Mail\FavoriteAdded;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Throwable;

class Catalog extends Component
{
    public string $categoryId = '';

    public function updatedCategoryId(): void
    {
        $this->validateOnly('categoryId');
    }

    public function toggleFavorite(int $productId): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $favorite = $user->favorites()->where('product_id', $productId)->first();

        if ($favorite !== null) {
            $favorite->delete();

            return;
        }

        try {
            $product = Http::baseUrl((string) config('services.platzi_store.url'))
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(10)
                ->retry([100, 300])
                ->get("products/{$productId}")
                ->throw()
                ->json();

            $favorite = $user->favorites()->create([
                'product_id' => $product['id'],
                'title' => $product['title'],
                'price' => $product['price'],
                'image' => $product['images'][0] ?? null,
                'category' => $product['category']['name'],
            ]);

            Mail::to($user)->send(new FavoriteAdded($favorite));
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('favorite', 'No fue posible actualizar favoritos.');
        }
    }

    public function render(): View
    {
        $products = [];
        $categories = [];
        $errorMessage = null;
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        try {
            $selectedCategoryId = $this->categoryId === '' ? null : (int) $this->categoryId;
            $query = $selectedCategoryId === null ? [] : ['categoryId' => $selectedCategoryId];

            $client = Http::baseUrl((string) config('services.platzi_store.url'))
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(10)
                ->retry([100, 300]);

            $products = $client->get('products', $query)->throw()->json();
            $categories = $client->get('categories')->throw()->json();
        } catch (Throwable $exception) {
            report($exception);
            $errorMessage = 'No fue posible cargar los productos. Intenta de nuevo más tarde.';
        }

        return view('livewire.products.catalog', [
            'products' => $products,
            'categories' => $categories,
            'errorMessage' => $errorMessage,
            'favoriteProductIds' => $user->favorites()->pluck('product_id')->all(),
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'categoryId' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
