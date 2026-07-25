<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('products', 'products.index')->name('products.index');
    Route::get('products/{productId}', function (int $productId) {
        return view('products.show', ['productId' => $productId]);
    })->whereNumber('productId')->name('products.show');
    Route::view('favorites', 'favorites.index')->name('favorites.index');
});

require __DIR__.'/settings.php';
