<?php

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\UserAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('auth')->group(function () {
    Route::post('login', [UserAuthController::class, 'loginPersonal']);
    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('login-pgct', [UserAuthController::class, 'loginPGCT']);
});


Route::prefix('auth')->middleware('auth:user-api')->group(function () {
    Route::get('logout', [UserAuthController::class, 'logout']);
    Route::get('city', function (){
        return 'city-user';
    });
});

Route::prefix('auth-admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'loginPersonal']);
    Route::post('login-pgct', [AdminAuthController::class, 'loginPGCT']);
});

Route::prefix('auth-admin')->middleware('auth:admin-api')->group(function () {
    Route::get('logout', [AdminAuthController::class, 'logout']);
    Route::get('country', function (){
        return 'country-admin';
    });
});
