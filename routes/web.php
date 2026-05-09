<?php

use Illuminate\Support\Facades\Route;
use Jeevanjoshi\LaravelAuditTrail\Http\Controllers\AuditTrailController;

Route::group([
    'prefix'     => config('audit-trail.route_prefix', 'audit-trail'),
    'middleware' => config('audit-trail.middleware', ['web', 'auth']),
    'as'         => 'audit-trail.',
], function () {

    Route::get('/', [AuditTrailController::class, 'index'])
        ->name('index');

    Route::get('/stats', [AuditTrailController::class, 'stats'])
        ->name('stats');

    Route::post('/revert/{audit}', [AuditTrailController::class, 'revert'])
        ->name('revert');

});
