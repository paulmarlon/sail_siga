<?php

use App\Http\Controllers\{ProfileController, GestionController, NivelController, ConfiguracionController, DomicilioController};
use Illuminate\Support\Facades\Route;

// Ruta principal protegida (Redirige al dashboard AdminLTE)
Route::get('/', function () {
    return view('home'); // Esta es tu vista con @extends('adminlte::page')
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas de Perfil (mantén las de Breeze para gestionar tu usuario)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Rutas para las gestiones
    Route::get('admin/gestiones', [\App\Http\Controllers\GestionController::class, 'index'])->name('admin.gestiones.index');
    Route::get('admin/gestiones/create', [\App\Http\Controllers\GestionController::class, 'create'])->name('admin.gestiones.create');
    Route::post('admin/gestiones', [\App\Http\Controllers\GestionController::class, 'store'])->name('admin.gestiones.store');
    Route::get('admin/gestiones/{gestion}/edit', [\App\Http\Controllers\GestionController::class, 'edit'])->name('admin.gestiones.edit');
    Route::put('admin/gestiones/{gestion}', [\App\Http\Controllers\GestionController::class, 'update'])->name('admin.gestiones.update');
    Route::delete('admin/gestiones/{gestion}', [\App\Http\Controllers\GestionController::class, 'destroy'])->name('admin.gestiones.destroy');
    Route::get('admin/gestiones/papelera', [\App\Http\Controllers\GestionController::class, 'papelera'])->name('admin.gestiones.papelera');
    Route::post('admin/gestiones/{id}/restaurar', [\App\Http\Controllers\GestionController::class, 'restaurar'])->name('admin.gestiones.restaurar');

    // Rutas para los niveles
    Route::get('admin/niveles', [\App\Http\Controllers\NivelController::class, 'index'])->name('admin.niveles.index');
    Route::post('admin/niveles', [\App\Http\Controllers\NivelController::class, 'store'])->name('admin.niveles.store');
    Route::get('admin/niveles/{nivel}/edit', [\App\Http\Controllers\NivelController::class, 'edit'])->name('admin.niveles.edit');
    Route::put('admin/niveles/{nivel}', [\App\Http\Controllers\NivelController::class, 'update'])->name('admin.niveles.update');
    Route::delete('admin/niveles/{nivel}', [\App\Http\Controllers\NivelController::class, 'destroy'])->name('admin.niveles.destroy');
    Route::get('admin/niveles/papelera', [\App\Http\Controllers\NivelController::class, 'papelera'])->name('admin.niveles.papelera');
    Route::post('admin/niveles/{id}/restaurar', [\App\Http\Controllers\NivelController::class, 'restaurar'])->name('admin.niveles.restaurar');

    // Cambia esto:
    // Route::resource('admin/configuracion', ...);

    // Por esto:
    Route::get('admin/configuracion/edit', [ConfiguracionController::class, 'edit'])->name('admin.configuracion.edit');
    Route::put('admin/configuracion/update', [ConfiguracionController::class, 'update'])->name('admin.configuracion.update');

    // 1. PRIMERO: Las rutas específicas (estáticas)
    Route::get('admin/personas/papelera', [\App\Http\Controllers\PersonaController::class, 'papelera'])
        ->name('admin.personas.papelera');

    Route::post('admin/personas/{id}/restaurar', [\App\Http\Controllers\PersonaController::class, 'restaurar'])
        ->name('admin.personas.restaurar');

    // 2. DESPUÉS: El resource que atrapa todo lo demás
    Route::resource('admin/personas', \App\Http\Controllers\PersonaController::class)
        ->names('admin.personas');
});
require __DIR__ . '/auth.php';
