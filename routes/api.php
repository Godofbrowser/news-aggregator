<?php

use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\NewsController;
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
Route::group([
    'prefix' => 'v1',
    'middleware' => [],
    'name' => 'api.',
], function() {
    Route::post('/auth/login', function () {
        return response()->json([
            'token' => 'test_token',
        ]);
    });
    Route::post('/auth/register', function () {
        return response()->json([
            'token' => 'test_token',
        ]);
    });

    Route::get('/news', [NewsController::class, 'index']);
    Route::get('/news/stats', [NewsController::class, 'getStats']);
    Route::get('/news/feed', [NewsController::class, 'getFeed']);

    Route::get('/sources', [NewsController::class, 'getSources']);

    Route::get('/categories', [CategoriesController::class, 'index']);
});
