<?php

use App\Http\Controllers\ContractController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Site public (réservation en ligne, type Airbnb)
// ---------------------------------------------------------------------------
Route::get('/', [PublicSiteController::class, 'index'])->name('public.home');
Route::get('/vehicule/{slug}', [PublicSiteController::class, 'show'])->name('public.vehicle');
Route::get('/vehicule/{vehicle}/disponibilites', [PublicSiteController::class, 'availability'])->name('public.availability');
Route::post('/reservation', [PublicSiteController::class, 'store'])->name('public.reserve');
Route::get('/reservation/{reference}/confirmation', [PublicSiteController::class, 'confirmation'])->name('public.confirmation');
Route::get('/reservation/{reference}/payer', [PublicSiteController::class, 'payNow'])->name('public.pay');
Route::get('/reservation/{reference}/paypal/retour', [PublicSiteController::class, 'paypalReturn'])->name('public.paypal.return');
Route::get('/reservation/{reference}/paypal/annuler', [PublicSiteController::class, 'paypalCancel'])->name('public.paypal.cancel');

// ---------------------------------------------------------------------------
// Contrat de location PDF (accès réservé à l'équipe connectée)
// ---------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/contrat/{booking}/telecharger', [ContractController::class, 'download'])->name('contract.download');
    Route::get('/contrat/{booking}/apercu', [ContractController::class, 'stream'])->name('contract.preview');
});
