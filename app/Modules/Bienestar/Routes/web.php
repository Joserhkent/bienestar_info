<?php

use Illuminate\Support\Facades\Route;

Route::prefix('bienestar')->name('bienestar.')->group(function () {
    Route::get('/', function () {
        return view('bienestar::welcome');
    })->name('home');
});
