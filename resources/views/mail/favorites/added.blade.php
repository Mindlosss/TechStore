<x-mail::message>
# Producto agregado a favoritos

Agregaste **{{ $favorite->title }}** a tu lista de favoritos.

Categoría: {{ $favorite->category }}  
Precio: ${{ number_format((float) $favorite->price, 2) }}

<x-mail::button :url="route('favorites.index')">
Ver mis favoritos
</x-mail::button>

Saludos,<br>
{{ config('app.name') }}
</x-mail::message>
