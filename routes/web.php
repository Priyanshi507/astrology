<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AstroController;
use App\Http\Controllers\FestivalController;
use App\Http\Controllers\MuhratController;
use App\Http\Controllers\GocharController;
use App\Http\Controllers\KundaliController;
use App\Http\Controllers\TarabalController;
use App\Http\Controllers\LandingController;

Route::get('/',               [LandingController::class, 'index'])->name('landing');
Route::get('/panchanga-data', [LandingController::class, 'panchangaData'])->name('panchanga.data');

Route::prefix('astro')->group(function () {

    // ── Core chart ────────────────────────────────────────────────
    Route::get('/',           [AstroController::class, 'index'])->name('astro.index');
    Route::post('/calculate', [AstroController::class, 'calculate'])->name('astro.calculate');
    Route::post('/panels',    [AstroController::class, 'panels'])->name('astro.panels');
    Route::post('/masa',      [AstroController::class, 'masa'])->name('astro.masa');
    Route::post('/varga',     [AstroController::class, 'varga'])->name('astro.varga');
    Route::get('/city',       [AstroController::class, 'city'])->name('astro.city');

    // ── Kundali ───────────────────────────────────────────────────
    Route::post('/kundali',   [KundaliController::class, 'kundali'])->name('astro.kundali');

    // ── Gochar (transits) ─────────────────────────────────────────
    Route::post('/gochar',    [GocharController::class, 'gochar'])->name('astro.gochar');

    // ── Muhrat ────────────────────────────────────────────────────
    Route::post('/muhrat',        [MuhratController::class, 'muhrat'])->name('astro.muhrat');
    Route::post('/muhrat/month',  [MuhratController::class, 'muhratMonth'])->name('astro.muhrat.month');
    Route::post('/muhrat/year',   [MuhratController::class, 'muhratYear'])->name('astro.muhrat.year');

    // ── Festivals / Today / Ekadashi ──────────────────────────────
    Route::post('/festivals', [FestivalController::class, 'festivals'])->name('astro.festivals');
    Route::post('/today',     [FestivalController::class, 'today'])->name('astro.today');
    Route::post('/ekadashi',  [FestivalController::class, 'ekadashiYear'])->name('astro.ekadashi');

    // ── Tarabal & Murti Nirnaya ───────────────────────────────────
    Route::post('/tarabal-murti', [TarabalController::class, 'tarabalMurti'])->name('astro.tarabal-murti');
});
