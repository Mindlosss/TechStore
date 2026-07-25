<?php

namespace App\Livewire\Products;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class Show extends Component
{
    #[Locked]
    public int $productId;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    public function toggleFavorite(): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $favorite = $user->favorites()->where('product_id', $this->productId)->first();

        if ($favorite !== null) {
            $favorite->delete();

            return;
        }

        try {
            $product = $this->getProduct();

            $user->favorites()->create([
                'product_id' => $product['id'],
                'title' => $product['title'],
                'price' => $product['price'],
                'image' => $product['images'][0] ?? null,
                'category' => $product['category']['name'],
            ]);
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('favorite', 'No fue posible actualizar favoritos.');
        }
    }

    public function render(): View
    {
        $product = null;
        $errorMessage = null;
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        try {
            $product = $this->getProduct();
        } catch (Throwable $exception) {
            report($exception);
            $errorMessage = 'No fue posible cargar el producto. Intenta de nuevo más tarde.';
        }

        return view('livewire.products.show', [
            'product' => $product,
            'errorMessage' => $errorMessage,
            'isFavorite' => $user->favorites()->where('product_id', $this->productId)->exists(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getProduct(): array
    {
        return Http::baseUrl((string) config('services.platzi_store.url'))
            ->acceptJson()
            ->connectTimeout(3)
            ->timeout(10)
            ->retry([100, 300])
            ->get("products/{$this->productId}")
            ->throw()
            ->json();
    }
}
