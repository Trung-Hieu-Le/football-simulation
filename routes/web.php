<?php

use App\Http\Controllers\Cup\EliminateCupController;
use App\Http\Controllers\Cup\EliminateMatchCupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tier\TeamTierController;
use App\Http\Controllers\Tier\SeasonTierController;
use App\Http\Controllers\Tier\MatchTierController;
use App\Http\Controllers\Tier\StatisticTierController;
use App\Http\Controllers\Cup\TeamCupController;
use App\Http\Controllers\Cup\SeasonCupController;
use App\Http\Controllers\Cup\MatchCupController;
use App\Http\Controllers\Cup\StatisticCupController;


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

Route::get("/", [SeasonCupController::class, 'index'])->name('cup.seasons.index');

Route::prefix('tier')->group(function () {
    Route::resource('teams', TeamTierController::class);
    Route::put('/teams/{id}', [TeamTierController::class, 'update'])->name('teams.update');

    Route::get('/', [SeasonTierController::class, 'index'])->name('seasons.index');
    Route::get('/seasons', [SeasonTierController::class, 'index'])->name('seasons.index');
    Route::get('/seasons/create', [SeasonTierController::class, 'create'])->name('seasons.create');
    Route::post('/seasons', [SeasonTierController::class, 'store'])->name('seasons.store');
    Route::get('/seasons/{id}', [SeasonTierController::class, 'show'])->name('seasons.show');
    Route::delete('/seasons/{id}', [SeasonTierController::class, 'destroy'])->name('seasons.destroy');
    Route::get('/matches/{id}', [SeasonTierController::class, 'listMatches'])->name('matches.show');
    Route::get('/histories/{id}', [SeasonTierController::class, 'showStatistics'])->name('histories.show');
    Route::post('/seasons/simulate', [MatchTierController::class, 'simulateMatch'])->name('seasons.simulate');

    Route::get('/statistics', [StatisticTierController::class, 'index'])->name('statistics.index');
});

Route::prefix('cup')->group(function () {
    Route::resource('teams', TeamCupController::class);
    Route::put('/teams/{id}', [TeamCupController::class, 'update'])->name('cup.teams.update');
    Route::post('/teams/reset-form', [TeamCupController::class, 'resetForm'])->name('cup.teams.resetForm');

    Route::get('/', [SeasonCupController::class, 'index'])->name('cup.seasons.index');
    Route::get('/seasons', [SeasonCupController::class, 'index'])->name('cup.seasons.index');
    Route::get('/seasons/create', [SeasonCupController::class, 'create'])->name('cup.seasons.create');
    Route::post('/seasons', [SeasonCupController::class, 'store'])->name('cup.seasons.store');
    Route::get('/seasons/{id}', [SeasonCupController::class, 'show'])->name('cup.seasons.show');
    Route::delete('/seasons/{id}', [SeasonCupController::class, 'destroy'])->name('cup.seasons.destroy');
    Route::get('/seasons-destroy-all', [SeasonCupController::class, 'destroyAll'])->name('cup.seasons.destroy_all');
    Route::get('/matches/{id}', [SeasonCupController::class, 'listMatches'])->name('cup.matches.show');
    Route::get('/histories/{id}', [SeasonCupController::class, 'showStatistics'])->name('cup.histories.show');
    Route::post('/seasons/simulate', [MatchCupController::class, 'simulateMatch'])->name('cup.seasons.simulate');
    Route::get('/seasons/{season}/eliminate', [SeasonCupController::class, 'createEliminateStage'])->name('cup.eliminate.create');

    Route::get('/statistics', [StatisticCupController::class, 'index'])->name('cup.statistics.index');
});

Route::prefix('cup/eliminate')->group(function () {
    Route::get('view/{season}', [EliminateCupController::class, 'view'])->name('cup.eliminate.view');
    Route::post('/simulate', [EliminateMatchCupController::class, 'simulateMatch'])->name('cup.seasons.eliminate.simulate');
    Route::get('/statistics/{seasonId}', [EliminateCupController::class, 'teamStatistics'])->name('cup.eliminate.statistics');


});
