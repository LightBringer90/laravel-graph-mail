<?php

use Illuminate\Support\Facades\Route;
use ProgressiveStudios\GraphMail\Http\Controllers\Api\MessageController;

// Webhook
Route::match(['GET', 'POST'], '/graph/webhook', [GraphWebhookController::class, 'handle'])->name('graph-mail.webhook');

$api = config('graph-mail.api');
Route::middleware($api['middleware'] ?? ['api'])
    ->prefix($api['prefix'] ?? 'graph-mail')
    ->as('graphmail.api.')
    ->group(function () {
        Route::post('/send', [MessageController::class, 'send'])->name('messages.send');
        Route::get('/messages/{id}', [MessageController::class, 'show'])->name('messages.show');
        Route::get('/health', [MessageController::class, 'health'])->name('health');
    });
