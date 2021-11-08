<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EntryController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\UserController;

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

    Route::resource('/user', UserController::class);
    Route::get('user-autosuggest/{name}', [UserController::class, 'autosuggest'])->name('user_autosuggest');

    Route::resource('/region', RegionController::class);
    Route::post('/region-rearrange', [RegionController::class, 'rearrange'])->name('region_rearrange');

    Route::resource('/comment', CommentController::class);
});
