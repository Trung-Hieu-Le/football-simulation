<?php

use App\Http\Controllers\Cup\EliminateCupController;
use App\Http\Controllers\Cup\EliminateMatchCupController;
use Illuminate\Support\Facades\Route;
<<<<<<< Updated upstream
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
    Route::post('/teams/reset-form', [TeamTierController::class, 'resetForm'])->name('tier.teams.resetForm');

    Route::get('/', [SeasonTierController::class, 'index'])->name('tier.seasons.index');
    Route::get('/seasons', [SeasonTierController::class, 'index'])->name('seasons.index');
    Route::get('/seasons/create', [SeasonTierController::class, 'create'])->name('seasons.create');
    Route::post('/seasons', [SeasonTierController::class, 'store'])->name('seasons.store');
    Route::get('/seasons/{id}', [SeasonTierController::class, 'show'])->name('seasons.show');
    Route::delete('/seasons/{id}', [SeasonTierController::class, 'destroy'])->name('seasons.destroy');
    Route::get('/seasons-destroy-all', [SeasonTierController::class, 'destroyAll'])->name('tier.seasons.destroy_all');
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
=======
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
>>>>>>> Stashed changes
