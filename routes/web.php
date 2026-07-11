<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Ruta principal protegida (Redirige al dashboard AdminLTE)
Route::get('/', function () {
    return view('home'); // Esta es tu vista con @extends('adminlte::page')
})->middleware(['auth', 'verified']);

// Rutas de Perfil (mantén las de Breeze para gestionar tu usuario)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
