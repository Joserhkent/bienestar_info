<?php

use Illuminate\Support\Facades\Route;

// Redirigir la raíz del proyecto directamente a nuestro módulo de Bienestar
Route::get('/', function () {
    return redirect()->route('bienestar.home');
});

Route::get('/login', function () {
    return redirect()->route('bienestar.login');
})->name('login');
