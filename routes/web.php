<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\LeagueController;


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

Route::get('/seasons', [SeasonController::class, 'index'])->name('seasons.index');
Route::get('/seasons/create', [SeasonController::class, 'store'])->name('seasons.create');
Route::delete('/seasons/{id}', [SeasonController::class, 'destroy'])->name('seasons.destroy');
Route::post('/seasons/{season}/group-stage', [SeasonController::class, 'groupStage'])->name('seasons.groupStage');
Route::post('/seasons/{season}/schedule', [SeasonController::class, 'generateSchedule'])->name('seasons.schedule');

Route::get('/league/{season_id}', [LeagueController::class, 'detail'])->name('league.detail');
Route::post('/league/{season_id}/simulate', [LeagueController::class, 'simulateNextMatches'])->name('league.simulate');
