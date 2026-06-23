<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Storefront SPA — all routes go to the same React entry
Route::get('/loja/{store_slug}', function () {
    return view('storefront');
})->where('store_slug', '[a-z0-9\-]+');

Route::get('/loja/{store_slug}/{any}', function () {
    return view('storefront');
})->where('store_slug', '[a-z0-9\-]+')->where('any', '.*');
