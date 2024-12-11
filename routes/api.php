<?php

use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

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

Route::group([
    'prefix' => 'v1',
    'middleware' => [],
    'name' => 'api.',
], function() {

    Route::group([
        'prefix' => 'auth',
    ], function() {
        require __DIR__.'/auth.php';
        Route::get('csrf-cookie', [CsrfCookieController::class, 'show']);
        Route::get('user', function (Request $request) {
            return $request->user();
        });
    });

    Route::group([
        'middleware' => ['auth:sanctum'],
    ], function() {

        Route::get('/news', [NewsController::class, 'index']);
        Route::get('/news/stats', [NewsController::class, 'getStats']);
        Route::get('/news/feed', [NewsController::class, 'getFeed']);

        Route::get('/sources', [NewsController::class, 'getSources']);

        Route::get('/categories', [CategoriesController::class, 'index']);
    });
});
