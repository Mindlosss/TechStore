<?php

namespace App\Livewire\Products;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Throwable;

class Catalog extends Component
{
    public string $categoryId = '';

    public function updatedCategoryId(): void
    {
        $this->validateOnly('categoryId');
    }

    public function render(): View
    {
        $products = [];
        $categories = [];
        $errorMessage = null;

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
