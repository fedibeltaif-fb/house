<?php

use App\Http\Controllers\PropertyController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'featured' => \App\Models\Property::where('is_featured', true)->take(3)->get()
    ]);
})->name('home');

Route::resource('properties', PropertyController::class);
Route::get('/search', [SearchController::class, 'index'])->name('properties.search');
