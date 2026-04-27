<?php

use App\Http\Controllers\PresupuestoController;
use Illuminate\Support\Facades\Route;

Route::post('/presupuestos', [PresupuestoController::class, 'store']);
