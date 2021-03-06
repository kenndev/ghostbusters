<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscribeController;

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

Route::get('/', function(){
    return "Hello world";
});

Route::post('/register',[AuthController::class,'register']);
Route::middleware('auth:sanctum')->post('/update-profile', [AuthController::class,'updateProfile']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'store']);
Route::post('/change-password', [AuthController::class, 'change_password']);
Route::middleware('auth:sanctum')->post('/subscribe', [SubscribeController::class, 'subScribe']);
