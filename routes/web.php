<?php

use App\Http\Controllers\BoardroomController;
use App\Http\Controllers\NegotiatorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RelayController;
use App\Http\Controllers\TimeMachineController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('/dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/boardroom8', [BoardroomController::class, 'index'])
        ->name('boardroom.index');

    Route::post('/boardroom8', [BoardroomController::class, 'store'])
        ->name('boardroom.store');

    Route::get('/time-machine8', [TimeMachineController::class, 'index'])
        ->name('time-machine.index');

    Route::get('/negotiator8', [NegotiatorController::class, 'index'])
        ->name('negotiator.index');

    Route::get('/relay8', [RelayController::class, 'index'])
        ->name('relay.index');

    Route::patch(
        '/relay8/{recommendation}/approve',
        [RelayController::class, 'approve']
    )->name('relay.approve');

    Route::patch(
        '/relay8/{recommendation}/reject',
        [RelayController::class, 'reject']
    )->name('relay.reject');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';