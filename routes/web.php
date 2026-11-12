<?php

use App\Http\Controllers\AsesorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KioscoController;
use App\Http\Controllers\PantallaController;
use App\Http\Controllers\ReporteController;
use App\Http\Middleware\SoloAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('inicio'))->name('inicio');

// Kiosco (ciudadano)
Route::get('/kiosco', [KioscoController::class, 'index'])->name('kiosco.index');
Route::post('/kiosco/turno', [KioscoController::class, 'store'])->name('kiosco.store');

// Pantalla pública (TV)
Route::get('/pantalla', [PantallaController::class, 'index'])->name('pantalla.index');
Route::get('/pantalla/datos', [PantallaController::class, 'datos'])->name('pantalla.datos');

// Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Panel del asesor
Route::middleware('auth')->prefix('asesor')->name('asesor.')->group(function () {
    Route::get('/', [AsesorController::class, 'index'])->name('index');
    Route::post('/modulo', [AsesorController::class, 'seleccionar'])->name('seleccionar');
    Route::post('/modulo/cambiar', [AsesorController::class, 'cambiarModulo'])->name('cambiar');
    Route::get('/estado', [AsesorController::class, 'estado'])->name('estado');
    Route::post('/llamar', [AsesorController::class, 'llamar'])->name('llamar');
    Route::post('/rellamar', [AsesorController::class, 'rellamar'])->name('rellamar');
    Route::post('/atender', [AsesorController::class, 'atender'])->name('atender');
    Route::post('/finalizar', [AsesorController::class, 'finalizar'])->name('finalizar');
    Route::post('/ausente', [AsesorController::class, 'ausente'])->name('ausente');
});

// Administración (solo admin)
Route::middleware(['auth', SoloAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes');
    Route::get('/reportes/csv', [ReporteController::class, 'csv'])->name('reportes.csv');
});
