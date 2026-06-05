<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AstroController;
use App\Http\Controllers\LandingController;

Route::get('/',               [LandingController::class, 'index'])->name('landing');
Route::get('/panchanga-data', [LandingController::class, 'panchangaData'])->name('panchanga.data');

Route::prefix('astro')->group(function () {
    Route::get('/',           [AstroController::class, 'index'])->name('astro.index');
    Route::post('/calculate', [AstroController::class, 'calculate'])->name('astro.calculate');
    Route::post('/masa',      [AstroController::class, 'masa'])->name('astro.masa');
    Route::get('/city',       [AstroController::class, 'city'])->name('astro.city');
    Route::post('/festivals', [AstroController::class, 'festivals'])->name('astro.festivals');
    Route::post('/today',     [AstroController::class, 'today'])->name('astro.today');
    Route::post('/muhrat',       [AstroController::class, 'muhrat'])->name('astro.muhrat');
    Route::post('/muhrat/month', [AstroController::class, 'muhratMonth'])->name('astro.muhrat.month');
    Route::post('/muhrat/year',  [AstroController::class, 'muhratYear'])->name('astro.muhrat.year');
    Route::post('/varga',     [AstroController::class, 'varga'])->name('astro.varga');
    Route::post('/ekadashi',  [AstroController::class, 'ekadashiYear'])->name('astro.ekadashi');
    Route::post('/gochar',    [AstroController::class, 'gochar'])->name('astro.gochar');
    Route::post('/tarabal-murti', [AstroController::class, 'tarabalMurti'])->name('astro.tarabal-murti');
});