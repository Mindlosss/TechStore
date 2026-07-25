<div class="flex flex-col gap-6">
    <div>
        <flux:button :href="route('products.index')" variant="ghost" wire:navigate>
            Volver a productos
        </flux:button>
    </div>

    @if ($errorMessage)
        <flux:callout variant="danger">
            {{ $errorMessage }}
        </flux:callout>
    @else
        <div class="grid gap-8 lg:grid-cols-2">
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($product['images'] as $image)
                    <img
                        class="aspect-square w-full rounded-xl border border-zinc-200 object-cover dark:border-zinc-700 {{ $loop->first ? 'sm:col-span-2' : '' }}"
                        src="{{ $image }}"
                        alt="{{ $product['title'] }}"
                        referrerpolicy="no-referrer"
                        wire:key="product-image-{{ $loop->index }}"
                    >
                @endforeach
            </div>

            <div class="flex flex-col gap-5">
                <flux:badge class="w-fit">
                    {{ $product['category']['name'] }}
                </flux:badge>

                <flux:heading size="xl">{{ $product['title'] }}</flux:heading>

                <flux:text class="text-2xl font-semibold text-zinc-900 dark:text-white">
                    {{ \Illuminate\Support\Number::currency((float) $product['price'], in: 'USD') }}
                </flux:text>

                <flux:separator />

                <div class="grid gap-2">
                    <flux:heading size="lg">Descripción</flux:heading>
                    <flux:text>{{ $product['description'] }}</flux:text>
                </div>
            </div>
        </div>
    @endif
</div>
