<?php

use Illuminate\Support\Facades\Route;
use ProgressiveStudios\GraphMail\Http\Controllers\Ui\DashboardController;
use ProgressiveStudios\GraphMail\Http\Controllers\Ui\MailTemplateController as UiMailTemplateController;
use ProgressiveStudios\GraphMail\Http\Controllers\Ui\OutboundMailController as UiMailController;

$ui = config('graph-mail.ui');
if (($ui['enabled'] ?? true) && ($ui['path'] ?? null)) {
    Route::middleware($ui['middleware'] ?? ['web', 'auth'])
        ->prefix($ui['path'])
        ->as('graphmail.')
        ->group(function () {

            Route::get('/', [DashboardController::class, 'index'])
                ->name('dashboard');
            Route::get('/data', [DashboardController::class, 'data'])
                ->name('dashboard.data');

            // Mails
            Route::get('/mails', [UiMailController::class, 'index'])
                ->name('mails.index');
            Route::get('/mails/{mail}', [UiMailController::class, 'show'])
                ->name('mails.show');

            //Templates
            Route::get('/templates', [UiMailTemplateController::class, 'index'])
                ->name('templates.index');

            Route::get('/templates/create', [UiMailTemplateController::class, 'create'])
                ->name('templates.create');

            Route::post('/templates', [UiMailTemplateController::class, 'store'])
                ->name('templates.store');

            Route::get('/templates/{template}/edit', [UiMailTemplateController::class, 'edit'])
                ->name('templates.edit');

            Route::put('/templates/{template}', [UiMailTemplateController::class, 'update'])
                ->name('templates.update');

            Route::delete('/templates/{template}', [UiMailTemplateController::class, 'destroy'])
                ->name('templates.destroy');

        });

}
