<?php

use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\PresupuestoController;
use Illuminate\Support\Facades\Route;

Route::get('/conceptos', [PresupuestoController::class, 'conceptos']);
Route::get('/servicios', [PresupuestoController::class, 'servicios']);
Route::post('/presupuestos', [PresupuestoController::class, 'store']);

Route::middleware(['web', 'auth', 'admin.only'])->prefix('admin')->group(function () {
	Route::get('/panel-data', [AdminPanelController::class, 'panelData']);

	Route::post('/tipos-presupuesto', [AdminPanelController::class, 'storeTipo']);
	Route::patch('/tipos-presupuesto/{tipo}', [AdminPanelController::class, 'updateTipo']);
	Route::delete('/tipos-presupuesto/{tipo}', [AdminPanelController::class, 'deleteTipo']);

	Route::post('/unidades', [AdminPanelController::class, 'storeUnidad']);
	Route::patch('/unidades/{unidad}', [AdminPanelController::class, 'updateUnidad']);
	Route::delete('/unidades/{unidad}', [AdminPanelController::class, 'deleteUnidad']);

	Route::post('/conceptos', [AdminPanelController::class, 'storeConcepto']);
	Route::patch('/conceptos/{concepto}', [AdminPanelController::class, 'updateConcepto']);
	Route::delete('/conceptos/{concepto}', [AdminPanelController::class, 'deleteConcepto']);

	Route::post('/presupuestos', [AdminPanelController::class, 'storePresupuesto']);
	Route::get('/presupuestos/{presupuesto}', [AdminPanelController::class, 'showPresupuesto']);
	Route::patch('/presupuestos/{presupuesto}/estado', [AdminPanelController::class, 'updateEstadoPresupuesto']);

	Route::post('/servicios', [AdminPanelController::class, 'storeServicio']);
	Route::patch('/servicios/{servicio}', [AdminPanelController::class, 'updateServicio']);
	Route::delete('/servicios/{servicio}', [AdminPanelController::class, 'deleteServicio']);

	Route::post('/albums', [AdminPanelController::class, 'storeAlbum']);
	Route::patch('/albums/{album}', [AdminPanelController::class, 'updateAlbum']);
	Route::delete('/albums/{album}', [AdminPanelController::class, 'deleteAlbum']);

	Route::post('/usuarios', [AdminPanelController::class, 'storeUsuario']);
	Route::patch('/usuarios/{usuario}', [AdminPanelController::class, 'updateUsuario']);
	Route::patch('/usuarios/{usuario}/toggle', [AdminPanelController::class, 'toggleUsuario']);
	Route::delete('/usuarios/{usuario}', [AdminPanelController::class, 'deleteUsuario']);
});
