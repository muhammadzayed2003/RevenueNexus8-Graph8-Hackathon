<?php

use App\Http\Controllers\BoardroomController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Graph8SyncController;
use App\Http\Controllers\Graph8WebhookController;
use App\Http\Controllers\NegotiatorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RelayController;
use App\Http\Controllers\TimeMachineController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::post(
    '/webhooks/graph8',
    [Graph8WebhookController::class, 'store']
)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('webhooks.graph8');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post(
        '/graph8/sync',
        [Graph8SyncController::class, 'sync']
    )->name('graph8.sync');

    Route::get('/boardroom8', [BoardroomController::class, 'index'])
        ->name('boardroom.index');

    Route::post('/boardroom8', [BoardroomController::class, 'store'])
        ->name('boardroom.store');

    Route::get('/time-machine8', [TimeMachineController::class, 'index'])
        ->name('time-machine.index');

    Route::post('/time-machine8', [TimeMachineController::class, 'store'])
        ->name('time-machine.store');

    Route::get('/negotiator8', [NegotiatorController::class, 'index'])
        ->name('negotiator.index');

    Route::post('/negotiator8', [NegotiatorController::class, 'store'])
        ->name('negotiator.store');

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

    Route::post(
        '/relay8/{recommendation}/execute',
        [RelayController::class, 'execute']
    )->name('relay.execute');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';
Route::post('/evidence8/chat', [\App\Http\Controllers\EvidenceChatController::class, 'store'])
    ->middleware('auth')
    ->name('evidence8.chat');