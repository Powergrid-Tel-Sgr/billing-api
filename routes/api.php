<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VendorController;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

Route::get('/test', function () {
    return "Alhumdullilah";
});

Route::post('/sign-in', [AuthController::class, 'signIn']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::prefix('nodes')->group(function () {
        Route::get('/', [NodeController::class, 'index']);
        Route::post('/', [NodeController::class, 'store']);
        Route::post('/import', [NodeController::class, 'import']);

        Route::get('/{node}', [NodeController::class, 'show']);
        Route::put('/{node}', [NodeController::class, 'update']);
        Route::delete('/{node}', [NodeController::class, 'destroy']);
    });

    Route::prefix('vendors')->group(function () {
        Route::get('/', [VendorController::class, 'index']);
    });

    Route::apiResource('services', ServiceController::class);
    Route::post('services/import', [ServiceController::class, 'import']);

    Route::prefix('/bill')->group(function () {
        Route::get('/', [BillController::class, 'index']);
        Route::post('/', [BillController::class, 'store']);
        Route::get('/export/{bill}/{index}', [BillController::class, 'export']);
    });
});
