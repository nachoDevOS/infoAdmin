<?php

use App\Http\Controllers\Api\RegistroController;
use App\Http\Controllers\Api\ConfirmacionApiController;
use Illuminate\Support\Facades\Route;

// Versión pública (sin token) — para auto-update
Route::get('/version', fn () => response()->json([
    'version' => config('app.client_version', '1.0.0'),
    'url'     => url('/api/download'),
]));
Route::get('/download', function () {
    $path = storage_path('app/client/infoDesk.exe');
    if (! file_exists($path)) {
        return response()->json(['error' => 'No hay actualización disponible'], 404);
    }
    return response()->download($path, 'infoDesk.exe');
});

// Rutas protegidas con token — llamadas desde los clientes Python MensaDesk
Route::middleware('api.token')->group(function () {
    Route::post('/registro', [RegistroController::class, 'store']);
    Route::post('/confirmacion', [ConfirmacionApiController::class, 'store']);
    Route::get('/pcs', [RegistroController::class, 'count']);
});
