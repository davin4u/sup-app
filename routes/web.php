<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/strava', 'App\Http\Controllers\StravaController@index');
Route::get('/strava/check', 'App\Http\Controllers\StravaController@check');

Route::middleware(['auth'])->group(function () {
    Route::namespace('App\Http\Controllers')->group(function () {
        /**
         * Activities
         */
        Route::get('/activities', 'ActivitiesController@index')
            ->name('activities.index');
        Route::get('/activities/upload-gpx', 'ActivitiesController@uploadGpx')
            ->name('activities.upload_gpx');
        Route::get('/activities/{activity}', 'ActivitiesController@show')
            ->name('activities.show');

        Route::post('/activities/store-gpx', 'ActivitiesController@storeGpx')
            ->name('activities.store_gpx');
    });

    /**
     * API
     */
    Route::namespace('App\Http\Controllers\Api')->group(function () {
        Route::get('/activities/{activity}/activity-data', 'ActivitiesController@getActivityData')
            ->name('activities.activity_data');
    });
});

require __DIR__.'/auth.php';

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
