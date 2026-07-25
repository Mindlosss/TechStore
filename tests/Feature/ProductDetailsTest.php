<?php

use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('guests cannot view product details', function () {
    $this->get(route('products.show', 12))
        ->assertRedirect(route('login'));
});

test('authenticated users can view product details', function () {
    Http::fake([
        '*/products/12' => Http::response(detailedProduct()),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('products.show', 12))
        ->assertSuccessful()
        ->assertSee('Classic Black Baseball Cap')
        ->assertSee('Clothes')
        ->assertSee('$58.00')
        ->assertSee('Elevate your casual wear')
        ->assertSee('https://i.imgur.com/product-one.jpeg', false)
        ->assertSee('https://i.imgur.com/product-two.jpeg', false)
        ->assertSee('referrerpolicy="no-referrer"', false);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.escuelajs.co/api/v1/products/12');
});

test('an error message is shown when a product cannot be loaded', function () {
    Http::fake([
        '*/products/999' => Http::response([], 404),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('products.show', 999))
        ->assertSuccessful()
        ->assertSee('No fue posible cargar el producto.');
});

/**
 * @return array<string, mixed>
 */
function detailedProduct(): array
{
    return [
        'id' => 12,
        'title' => 'Classic Black Baseball Cap',
        'price' => 58,
        'description' => 'Elevate your casual wear with this timeless black baseball cap.',
        'images' => [
            'https://i.imgur.com/product-one.jpeg',
            'https://i.imgur.com/product-two.jpeg',
        ],
        'category' => [
            'id' => 1,
            'name' => 'Clothes',
        ],
    ];
}
