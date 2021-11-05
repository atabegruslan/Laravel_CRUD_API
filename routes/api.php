<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\RegionController;

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

Route::group(['as' => 'api.', 'middleware' => ['auth:api', 'devcors']], function () {
    Route::resource('/entry', EntryController::class);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::resource('/region', RegionController::class);
    Route::post('/region-rearrange', [RegionController::class, 'rearrange'])->name('region_rearrange');
});
