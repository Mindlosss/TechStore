<div class="flex flex-col gap-6">
    <div class="grid gap-1">
        <flux:heading size="xl">Mis favoritos</flux:heading>
        <flux:text>Productos guardados en tu lista.</flux:text>
    </div>

    @if ($favorites->isEmpty())
        <flux:callout>
            Aún no tienes productos favoritos.
        </flux:callout>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($favorites as $favorite)
                <article
                    class="flex flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
                    wire:key="favorite-{{ $favorite->id }}"
                >
                    <a class="flex-1" href="{{ route('products.show', $favorite->product_id) }}" wire:navigate>
                        <img
                            class="aspect-square w-full object-cover"
                            src="{{ $favorite->image ?? '' }}"
                            alt="{{ $favorite->title }}"
                            referrerpolicy="no-referrer"
                        >

                        <div class="grid gap-2 p-4">
                            <flux:badge size="sm" class="w-fit">{{ $favorite->category }}</flux:badge>
                            <flux:heading size="lg">{{ $favorite->title }}</flux:heading>
                            <flux:text class="font-semibold text-zinc-900 dark:text-white">
                                {{ \Illuminate\Support\Number::currency((float) $favorite->price, in: 'USD') }}
                            </flux:text>
                        </div>
                    </a>

                    <div class="p-4 pt-0">
                        <flux:button class="w-full" variant="danger" wire:click="remove({{ $favorite->id }})">
                            Quitar de favoritos
                        </flux:button>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
