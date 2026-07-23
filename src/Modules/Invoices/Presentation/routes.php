<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Presentation\Http\InvoiceCreateController;
use Modules\Invoices\Presentation\Http\InvoiceSendController;
use Modules\Invoices\Presentation\Http\InvoiceViewController;
use Ramsey\Uuid\Validator\GenericValidator;

Route::prefix('invoice')->group(function () {
    Route::pattern('invoiceId', (new GenericValidator)->getPattern());
    Route::post('/', InvoiceCreateController::class)->name('invoice.create');
    Route::get('/{invoiceId}', InvoiceViewController::class)->name('invoice.get');
    Route::post('/{invoiceId}/send', InvoiceSendController::class)->name('invoice.send');
});
