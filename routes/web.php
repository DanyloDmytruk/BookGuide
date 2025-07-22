<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/books');
});

Route::get('books', function () {
    return Inertia::render('Books');
})->middleware(['auth', 'verified'])->name('books');

Route::get('authors', function () {
    return Inertia::render('Authors');
})->middleware(['auth', 'verified'])->name('authors');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
