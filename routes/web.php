<?php

use App\Http\Controllers\ContractController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Site public (réservation en ligne, type Airbnb)
// ---------------------------------------------------------------------------
Route::get('/', [PublicSiteController::class, 'index'])->name('public.home');
Route::get('/vehicule/{slug}', [PublicSiteController::class, 'show'])->name('public.vehicle');
Route::get('/vehicule/{slug}/reserver', [PublicSiteController::class, 'book'])->name('public.book');
Route::post('/reservation/calcul', [PublicSiteController::class, 'calculatePrice'])->name('public.calculate');
Route::get('/reservation/{token}/documents', [PublicSiteController::class, 'documents'])->name('public.documents');
Route::post('/reservation/{token}/documents', [PublicSiteController::class, 'storeDocuments'])->name('public.documents.store');
Route::get('/vehicule/{vehicle}/disponibilites', [PublicSiteController::class, 'availability'])->name('public.availability');
Route::post('/reservation', [PublicSiteController::class, 'store'])->name('public.reserve');
Route::get('/conditions', [PublicSiteController::class, 'terms'])->name('public.terms');
Route::get('/blog', [PublicSiteController::class, 'blogIndex'])->name('public.blog');
Route::get('/blog/{slug}', [PublicSiteController::class, 'blogShow'])->name('public.blog.show');
Route::post('/newsletter', [PublicSiteController::class, 'subscribeNewsletter'])->name('public.newsletter');
Route::post('/track', [PublicSiteController::class, 'track'])->name('public.track');
Route::get('/sitemap.xml', [PublicSiteController::class, 'sitemap'])->name('public.sitemap');
Route::get('/page/{slug}', [PublicSiteController::class, 'page'])->name('public.page');
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
