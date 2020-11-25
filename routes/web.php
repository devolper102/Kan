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
Auth::routes();
Route::get('/home', function () {
    return redirect()->route('tasks.index');
})->name('home');
//Route::get('/home', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => 'auth'], function () {
    Route::get('tasks', [\App\Http\Controllers\TaskController::class,'index'])->name('tasks.index');
    Route::post('tasks', [\App\Http\Controllers\TaskController::class,'store'])->name('tasks.store');
    Route::put('tasks/sync', [\App\Http\Controllers\TaskController::class,'sync'])->name('tasks.sync');
    Route::post('tasks/update', [\App\Http\Controllers\TaskController::class,'update'])->name('tasks.update');

    Route::get('export', [\App\Http\Controllers\TaskController::class,'export'])->name('tasks.export');
});

Route::group(['middleware' => 'auth'], function () {
    Route::post('statuses', [\App\Http\Controllers\StatusController::class,'store'])->name('statuses.store');
    Route::put('statuses', [\App\Http\Controllers\StatusController::class,'update'])->name('statuses.update');

     Route::get('statuses/delete/{id}', [\App\Http\Controllers\StatusController::class,'delete'])->name('statuses.delete');
});

