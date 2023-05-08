<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\StationController;
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

Route::controller(StationController::class)
    ->prefix('stations')
    ->group(function () {
        Route::get('/', 'show');
        Route::get('/{company}', 'show');
        Route::post('/', 'create');
        Route::put('/{station}', 'update');
        Route::delete('/{station}', 'delete');
    });

Route::controller(CompanyController::class)
    ->prefix('companies')
    ->group(function () {
        Route::get('/', 'show');
        Route::post('/', 'create');
        Route::put('/{company}', 'update');
        Route::delete('/{company}', 'delete');
    });
//});
