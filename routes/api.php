<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{OrdersController, PaymentsController};
use App\Http\Controllers\Auth\{LoginController, RegisterController};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/auth/register', RegisterController::class)->name('auth.register');
Route::post('/auth/login', LoginController::class)->name('auth.login');

Route::middleware(['jwt.auth', 'throttle:60,1'])->group(function () {
    Route::post('/orders', [OrdersController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/payments', [PaymentsController::class, 'store'])->name('payments.store');
});
