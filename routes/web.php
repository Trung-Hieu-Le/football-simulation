<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\StatisticController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::resource('teams', TeamController::class);
Route::put('/teams/{id}', [TeamController::class, 'update'])->name('teams.update');

Route::get('/', [SeasonController::class, 'index'])->name('seasons.index');
Route::get('/seasons', [SeasonController::class, 'index'])->name('seasons.index');
Route::get('/seasons/create', [SeasonController::class, 'create'])->name('seasons.create');
Route::post('/seasons', [SeasonController::class, 'store'])->name('seasons.store');
Route::get('/seasons/{id}', [SeasonController::class, 'show'])->name('seasons.show');
Route::delete('/seasons/{id}', [SeasonController::class, 'destroy'])->name('seasons.destroy');
Route::get('/matches/{id}', [SeasonController::class, 'listMatches'])->name('matches.show');
Route::get('/histories/{id}', [SeasonController::class, 'showStatistics'])->name('histories.show');
Route::post('/seasons/simulate', [MatchController::class, 'simulateMatch'])->name('seasons.simulate');

Route::get('/statistics', [StatisticController::class, 'index'])->name('statistics.index');
