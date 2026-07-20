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
use App\Http\Controllers\Cup\StatisticController as CupStatisticController;
use App\Http\Controllers\StatisticController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/statistics/all-time', [StatisticController::class, 'allTime'])->name('statistics.all-time');

Route::prefix('teams')->name('teams.')->group(function () {
    Route::get('/', [TeamController::class, 'index'])->name('index');
    Route::post('/', [TeamController::class, 'store'])->name('store');
    Route::put('/{id}', [TeamController::class, 'update'])->name('update');
    Route::delete('/{id}', [TeamController::class, 'destroy'])->name('destroy');
    Route::post('/reset-elo', [TeamController::class, 'resetAllElo'])->name('reset-elo');
});

Route::prefix('league')->name('league.')->group(function () {
    Route::get('/', [LeagueSeasonController::class, 'index'])->name('seasons.index');
    Route::get('/seasons/create', [LeagueSeasonController::class, 'create'])->name('seasons.create');
    Route::post('/seasons', [LeagueSeasonController::class, 'store'])->name('seasons.store');
    Route::get('/seasons/{id}', [LeagueSeasonController::class, 'show'])->name('seasons.show');
    Route::delete('/seasons/{id}', [LeagueSeasonController::class, 'destroy'])->name('seasons.destroy');
    Route::post('/seasons/destroy-all', [LeagueSeasonController::class, 'destroyAll'])->name('seasons.destroy-all');
    Route::post('/seasons/{id}/calculate-results', [LeagueSeasonController::class, 'calculateResults'])->name('seasons.calculate-results');

    Route::get('/seasons/{seasonId}/matches', [LeagueMatchController::class, 'index'])->name('matches.index');
    Route::get('/matches/{matchId}', [LeagueMatchController::class, 'show'])->name('matches.show');
    Route::post('/seasons/{seasonId}/matches/round/{round}/simulate', [LeagueMatchController::class, 'simulate'])->name('matches.simulate-round');
    Route::post('/seasons/{seasonId}/matches/simulate-all', [LeagueMatchController::class, 'simulateAll'])->name('matches.simulate-all');
    Route::post('/seasons/{seasonId}/matches/round/{round}/reset', [LeagueMatchController::class, 'reset'])->name('matches.reset-round');

    Route::get('/seasons/{seasonId}/statistics', [LeagueStatisticController::class, 'index'])->name('statistics.index');
    Route::redirect('/statistics/all-time', '/statistics/all-time');
});

Route::prefix('cup')->name('cup.')->group(function () {
    Route::get('/', [CupSeasonController::class, 'index'])->name('seasons.index');
    Route::get('/seasons/create', [CupSeasonController::class, 'create'])->name('seasons.create');
    Route::post('/seasons', [CupSeasonController::class, 'store'])->name('seasons.store');
    Route::get('/seasons/{id}', [CupSeasonController::class, 'show'])->name('seasons.show');
    Route::delete('/seasons/{id}', [CupSeasonController::class, 'destroy'])->name('seasons.destroy');
    Route::post('/seasons/destroy-all', [CupSeasonController::class, 'destroyAll'])->name('seasons.destroy-all');
    Route::post('/seasons/{id}/advance-knockout', [CupSeasonController::class, 'advanceToKnockout'])->name('seasons.advance-knockout');

    Route::get('/seasons/{seasonId}/matches', [CupMatchController::class, 'index'])->name('matches.index');
    Route::get('/matches/{matchId}', [CupMatchController::class, 'show'])->name('matches.show');
    Route::post('/seasons/{seasonId}/matches/round/{round}/simulate', [CupMatchController::class, 'simulate'])->name('matches.simulate-round');
    Route::post('/seasons/{seasonId}/matches/simulate-all', [CupMatchController::class, 'simulateAll'])->name('matches.simulate-all');

    Route::get('/seasons/{seasonId}/eliminate', [CupEliminateController::class, 'index'])->name('eliminate.index');
    Route::get('/eliminate/{matchId}', [CupEliminateController::class, 'show'])->name('eliminate.show');
    Route::post('/seasons/{seasonId}/eliminate/{round}/simulate', [CupEliminateController::class, 'simulateRound'])->name('eliminate.simulate-round');

    Route::get('/seasons/{seasonId}/statistics', [CupStatisticController::class, 'index'])->name('statistics.index');
    Route::redirect('/statistics/all-time', '/statistics/all-time');
});

Route::redirect('/tier', '/league');
