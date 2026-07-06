<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\League\SeasonController as LeagueSeasonController;
use App\Http\Controllers\League\MatchController as LeagueMatchController;
use App\Http\Controllers\League\StatisticController as LeagueStatisticController;
use App\Http\Controllers\Cup\SeasonController as CupSeasonController;
use App\Http\Controllers\Cup\MatchController as CupMatchController;
use App\Http\Controllers\Cup\EliminateController as CupEliminateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home & Mode Selection
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/select-mode', [HomeController::class, 'selectMode'])->name('select-mode');

// Unified Teams Management
Route::prefix('teams')->name('teams.')->group(function () {
    Route::get('/', [TeamController::class, 'index'])->name('index');
    Route::get('/create', [TeamController::class, 'create'])->name('create');
    Route::post('/', [TeamController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [TeamController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TeamController::class, 'update'])->name('update');
    Route::delete('/{id}', [TeamController::class, 'destroy'])->name('destroy');
    Route::post('/reset-elo', [TeamController::class, 'resetAllElo'])->name('reset-elo');
    Route::post('/reset-form', [TeamController::class, 'resetAllForm'])->name('reset-form');
});

// League Routes (renamed from Tier)
Route::prefix('league')->name('league.')->group(function () {
    // Seasons
    Route::get('/', [LeagueSeasonController::class, 'index'])->name('seasons.index');
    Route::get('/seasons', [LeagueSeasonController::class, 'index'])->name('seasons.list');
    Route::get('/seasons/create', [LeagueSeasonController::class, 'create'])->name('seasons.create');
    Route::post('/seasons', [LeagueSeasonController::class, 'store'])->name('seasons.store');
    Route::get('/seasons/{id}', [LeagueSeasonController::class, 'show'])->name('seasons.show');
    Route::delete('/seasons/{id}', [LeagueSeasonController::class, 'destroy'])->name('seasons.destroy');
    Route::post('/seasons/{id}/calculate-results', [LeagueSeasonController::class, 'calculateResults'])->name('seasons.calculate-results');
    
    // Matches
    Route::get('/seasons/{seasonId}/matches', [LeagueMatchController::class, 'index'])->name('matches.index');
    Route::get('/matches/{matchId}', [LeagueMatchController::class, 'show'])->name('matches.show');
    Route::post('/seasons/{seasonId}/matches/round/{round}/simulate', [LeagueMatchController::class, 'simulate'])->name('matches.simulate-round');
    Route::post('/seasons/{seasonId}/matches/simulate-all', [LeagueMatchController::class, 'simulateAll'])->name('matches.simulate-all');
    Route::post('/seasons/{seasonId}/matches/round/{round}/reset', [LeagueMatchController::class, 'reset'])->name('matches.reset-round');
    
    // Statistics
    Route::get('/seasons/{seasonId}/statistics', [LeagueStatisticController::class, 'index'])->name('statistics.index');
    Route::get('/statistics/all-time', [LeagueStatisticController::class, 'allTimeStats'])->name('statistics.all-time');
});

// Cup Routes
Route::prefix('cup')->name('cup.')->group(function () {
    // Seasons
    Route::get('/', [CupSeasonController::class, 'index'])->name('seasons.index');
    Route::get('/seasons', [CupSeasonController::class, 'index'])->name('seasons.list');
    Route::get('/seasons/create', [CupSeasonController::class, 'create'])->name('seasons.create');
    Route::post('/seasons', [CupSeasonController::class, 'store'])->name('seasons.store');
    Route::get('/seasons/{id}', [CupSeasonController::class, 'show'])->name('seasons.show');
    Route::delete('/seasons/{id}', [CupSeasonController::class, 'destroy'])->name('seasons.destroy');
    Route::post('/seasons/{id}/advance-knockout', [CupSeasonController::class, 'advanceToKnockout'])->name('seasons.advance-knockout');
    
    // Group Stage Matches
    Route::get('/seasons/{seasonId}/matches', [CupMatchController::class, 'index'])->name('matches.index');
    Route::get('/matches/{matchId}', [CupMatchController::class, 'show'])->name('matches.show');
    Route::post('/seasons/{seasonId}/matches/round/{round}/simulate', [CupMatchController::class, 'simulate'])->name('matches.simulate-round');
    Route::post('/seasons/{seasonId}/matches/simulate-all', [CupMatchController::class, 'simulateAll'])->name('matches.simulate-all');
    
    // Knockout Stage
    Route::get('/seasons/{seasonId}/eliminate', [CupEliminateController::class, 'index'])->name('eliminate.index');
    Route::get('/eliminate/{matchId}', [CupEliminateController::class, 'show'])->name('eliminate.show');
    Route::post('/seasons/{seasonId}/eliminate/{round}/simulate', [CupEliminateController::class, 'simulateRound'])->name('eliminate.simulate-round');
});

// Legacy routes for backward compatibility (optional - can be removed after migration)
Route::redirect('/tier', '/league');
Route::redirect('/tier/seasons', '/league/seasons');
