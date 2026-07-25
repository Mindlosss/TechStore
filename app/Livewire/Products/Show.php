<?php

namespace App\Livewire\Products;

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

    public function render(): View
    {
        $product = null;
        $errorMessage = null;

        try {
            $product = Http::baseUrl((string) config('services.platzi_store.url'))
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(10)
                ->retry([100, 300])
                ->get("products/{$this->productId}")
                ->throw()
                ->json();
        } catch (Throwable $exception) {
            report($exception);
            $errorMessage = 'No fue posible cargar el producto. Intenta de nuevo más tarde.';
        }

        return view('livewire.products.show', [
            'product' => $product,
            'errorMessage' => $errorMessage,
        ]);
    }
}
