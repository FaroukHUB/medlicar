<?php

use App\Http\Controllers\ContractController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Contrat de location PDF (accès réservé à l'équipe connectée)
Route::middleware('auth')->group(function () {
    Route::get('/contrat/{booking}/telecharger', [ContractController::class, 'download'])->name('contract.download');
    Route::get('/contrat/{booking}/apercu', [ContractController::class, 'stream'])->name('contract.preview');
});
