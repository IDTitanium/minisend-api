<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('emails')->group(function() {
    Route::get('/receiver', [\App\Http\Controllers\Api\EmailNotificationController::class, 'getEmailsByReceiver']);
    Route::get('/stats', [\App\Http\Controllers\Api\EmailNotificationController::class, 'getEmailStats']);
    Route::get('', [\App\Http\Controllers\Api\EmailNotificationController::class, 'getAll']);
    Route::get('{uuid}', [\App\Http\Controllers\Api\EmailNotificationController::class, 'get']);
    Route::post('', [\App\Http\Controllers\Api\EmailNotificationController::class, 'create']);
});
