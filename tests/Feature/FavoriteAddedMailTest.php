<?php

use App\Mail\FavoriteAdded;
use App\Models\Favorite;

test('favorite added email contains the product information', function () {
    $favorite = Favorite::factory()->create([
        'title' => 'Classic Black Baseball Cap',
        'price' => 58,
        'category' => 'Clothes',
    ]);

    $mail = new FavoriteAdded($favorite);

    expect($mail->envelope()->subject)->toBe('Producto agregado a favoritos');

    $mail
        ->assertSeeInHtml('Classic Black Baseball Cap')
        ->assertSeeInHtml('Clothes')
        ->assertSeeInHtml('$58.00');
});
