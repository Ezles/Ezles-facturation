<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ClientController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [InvoiceController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Invoice routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{id}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/invoices/{id}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/invoices/export/excel', [InvoiceController::class, 'export'])->name('invoices.export');
    Route::get('/invoices/export/unpaid', [InvoiceController::class, 'exportUnpaid'])->name('invoices.export.unpaid');
    Route::post('/invoices/{id}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.markAsPaid');
    Route::post('/invoices/{id}/send-email', [InvoiceController::class, 'sendByEmail'])->name('invoices.sendEmail');
});

// Routes pour les devis
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::get('/quotes/{id}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::get('/quotes/{id}/edit', [QuoteController::class, 'edit'])->name('quotes.edit');
    Route::put('/quotes/{id}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::delete('/quotes/{id}', [QuoteController::class, 'destroy'])->name('quotes.destroy');
    Route::get('/quotes/{id}/pdf', [QuoteController::class, 'generatePdf'])->name('quotes.pdf');
    Route::get('/quotes/{id}/download', [QuoteController::class, 'downloadPdf'])->name('quotes.download');
    Route::post('/quotes/{id}/send', [QuoteController::class, 'sendByEmail'])->name('quotes.send');
    Route::post('/quotes/{id}/accept', [QuoteController::class, 'markAsAccepted'])->name('quotes.accept');
    Route::post('/quotes/{id}/reject', [QuoteController::class, 'markAsRejected'])->name('quotes.reject');
    Route::post('/quotes/{id}/convert', [QuoteController::class, 'convertToInvoice'])->name('quotes.convert');
});

// Routes pour les clients
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{id}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{id}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{id}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
