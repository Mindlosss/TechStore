<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="grid gap-1">
            <flux:heading size="xl">Productos</flux:heading>
            <flux:text>Explora el catálogo de TechStore.</flux:text>
        </div>

        <div class="w-full sm:w-64">
            <flux:select wire:model.live="categoryId" label="Categoría">
                <option value="">Todas las categorías</option>

                @foreach ($categories as $category)
                    <option value="{{ $category['id'] }}" wire:key="category-{{ $category['id'] }}">
                        {{ $category['name'] }}
                    </option>
                @endforeach
            </flux:select>

            <flux:error name="categoryId" />
        </div>
    </div>

    @if ($errorMessage)
        <flux:callout variant="danger">
            {{ $errorMessage }}
        </flux:callout>
    @elseif ($products === [])
        <flux:callout>
            No se encontraron productos para esta categoría.
        </flux:callout>
    @else
        <div
            class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            wire:loading.class="opacity-50"
            wire:target="categoryId"
        >
            @foreach ($products as $product)
                <article
                    class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
                    wire:key="product-{{ $product['id'] }}"
                >
                    <a class="block h-full" href="{{ route('products.show', $product['id']) }}" wire:navigate>
                        <img
                            class="aspect-square w-full object-cover"
                            src="{{ $product['images'][0] ?? '' }}"
                            alt="{{ $product['title'] }}"
                            referrerpolicy="no-referrer"
                        >

                        <div class="grid gap-2 p-4">
                            <flux:badge size="sm" class="w-fit">
                                {{ $product['category']['name'] }}
                            </flux:badge>

                            <flux:heading size="lg">{{ $product['title'] }}</flux:heading>

                            <flux:text class="font-semibold text-zinc-900 dark:text-white">
                                {{ \Illuminate\Support\Number::currency((float) $product['price'], in: 'USD') }}
                            </flux:text>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    @endif
</div>
