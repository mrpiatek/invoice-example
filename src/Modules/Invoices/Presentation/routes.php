<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Presentation\Http\InvoiceController;
use Ramsey\Uuid\Validator\GenericValidator;

Route::controller(InvoiceController::class)->prefix('invoice')->group(function () {
    Route::pattern('invoiceId', (new GenericValidator)->getPattern());
    Route::post('/', 'create')->name('invoice.create');
    Route::get('/{invoiceId}', 'get')->name('invoice.get');
    Route::post('/{invoiceId}/send', 'send')->name('invoice.send');
});
