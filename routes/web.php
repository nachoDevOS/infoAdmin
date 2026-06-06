<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\ConfirmacionesController;
use App\Http\Controllers\TipoMensajeController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route(auth()->user()->firstAllowedRoute()));
    Route::get('/panel', [PanelController::class, 'index'])->middleware('permission:mensajes.enviar')->name('panel');
    Route::post('/panel/enviar', [PanelController::class, 'enviar'])->middleware('permission:mensajes.enviar')->name('panel.enviar');
    Route::get('/panel/pcs', [PanelController::class, 'pcs'])->name('panel.pcs');
    Route::get('/panel/pcs-lista', [PanelController::class, 'pcsLista'])->name('panel.pcs-lista');
    Route::get('/historial', [HistorialController::class, 'index'])->middleware('permission:historial.ver')->name('historial');
    Route::get('/historial/data', [HistorialController::class, 'data'])->middleware('permission:historial.ver')->name('historial.data');
    Route::get('/confirmaciones', [ConfirmacionesController::class, 'index'])->middleware('permission:confirmaciones.ver')->name('confirmaciones');
    Route::get('/confirmaciones/data', [ConfirmacionesController::class, 'data'])->middleware('permission:confirmaciones.ver')->name('confirmaciones.data');
    Route::get('/confirmaciones/export', [ConfirmacionesController::class, 'exportCsv'])->middleware('permission:confirmaciones.ver')->name('confirmaciones.export');
    Route::post('/confirmaciones/reenviar', [ConfirmacionesController::class, 'reenviar'])->middleware('permission:mensajes.enviar')->name('confirmaciones.reenviar');
Route::get('/tipos', [TipoMensajeController::class, 'index'])->middleware('permission:tipos.gestionar')->name('tipos.index');
    Route::post('/tipos', [TipoMensajeController::class, 'store'])->middleware('permission:tipos.gestionar')->name('tipos.store');
    Route::put('/tipos/{tipo}', [TipoMensajeController::class, 'update'])->middleware('permission:tipos.gestionar')->name('tipos.update');
    Route::delete('/tipos/{tipo}', [TipoMensajeController::class, 'destroy'])->middleware('permission:tipos.gestionar')->name('tipos.destroy');
    Route::get('/usuarios', [UsuarioController::class, 'index'])->middleware('permission:usuarios.gestionar')->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->middleware('permission:usuarios.gestionar')->name('usuarios.store');
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->middleware('permission:usuarios.gestionar')->name('usuarios.destroy');
    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:roles.gestionar')->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:roles.gestionar')->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.gestionar')->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.gestionar')->name('roles.destroy');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
