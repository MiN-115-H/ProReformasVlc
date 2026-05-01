<?php

use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::view('/login', 'app')->name('login');

Route::prefix('auth')->group(function () {
	Route::get('/me', [AdminAuthController::class, 'me']);
	Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:10,1');
	Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth');
});

Route::view('/admin/panel', 'app')->middleware(['auth', 'admin.only']);

Route::view('/{any?}', 'app')->where('any', '^(?!auth\/).*$');
