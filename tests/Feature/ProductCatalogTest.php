<?php

use App\Livewire\Products\Catalog;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('guests cannot visit the product catalog', function () {
    $this->get(route('products.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view the product catalog', function () {
    Http::fake([
        '*/products' => Http::response([
            product(1, 'Laptop', 1200, 'Electronics'),
        ]),
        '*/categories' => Http::response([
            ['id' => 2, 'name' => 'Electronics'],
        ]),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('products.index'))
        ->assertSuccessful()
        ->assertSee('Laptop')
        ->assertSee('Electronics')
        ->assertSee('$1,200.00')
        ->assertSee('referrerpolicy="no-referrer"', false)
        ->assertDontSee('loading="lazy"', false);
});

test('products can be filtered by category without reloading the page', function () {
    Http::fake(function (Request $request) {
        if (str_ends_with($request->url(), '/categories')) {
            return Http::response([
                ['id' => 1, 'name' => 'Clothes'],
                ['id' => 2, 'name' => 'Electronics'],
            ]);
        }

        $categoryId = $request->data()['categoryId'] ?? null;

        return Http::response([
            $categoryId === 2
                ? product(2, 'Headphones', 80, 'Electronics')
                : product(1, 'T-Shirt', 20, 'Clothes'),
        ]);
    });

    Livewire::actingAs(User::factory()->create())
        ->test(Catalog::class)
        ->assertSee('T-Shirt')
        ->set('categoryId', '2')
        ->assertSee('Headphones')
        ->assertDontSee('T-Shirt');

    Http::assertSent(fn (Request $request): bool => ($request->data()['categoryId'] ?? null) === 2);
});

/**
 * @return array<string, mixed>
 */
function product(int $id, string $title, int $price, string $category): array
{
    return [
        'id' => $id,
        'title' => $title,
        'price' => $price,
        'images' => ['https://example.com/product.jpg'],
        'category' => ['id' => $id, 'name' => $category],
    ];
}
