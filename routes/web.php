<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\EntryController;
use App\Http\Controllers\Web\RegionController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\MiscController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\ActivityLogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth', 'spatie.permission']], function () {
    Route::resource('/entry', EntryController::class);
    Route::resource('/region', RegionController::class);

    Route::resource('/user', UserController::class);
    Route::resource('/role', RoleController::class);

    Route::post('/contact', [MiscController::class, 'contact'])->name('contact');
    Route::get('/contactform', [MiscController::class, 'contactform'])->name('contactform');

    Route::resource('/activity', ActivityLogController::class);

});
    
Route::get('/markAsRead', [NotificationController::class, 'markAsRead']);
Route::post('/notification', [NotificationController::class, 'store']);
