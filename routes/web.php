<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarkerController;
use Inertia\Inertia;

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
Route::get('/', [MarkerController::class, 'visitor'])->name('homepage');
Route::post('/visitor/markers', [MarkerController::class, 'getMarkers']);
Route::post('/visitor/{marker}', [MarkerController::class, 'displayMarker']);


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');




Route::middleware('auth')->prefix('/sites')->group(function (){
    Route::get('/', [MarkerController::class, 'get'])->name('sites');
    Route::post('/', [MarkerController::class, 'create']);
    Route::post('/{marker}', [MarkerController::class, 'edit']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
