<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\View;

use Modules\Invoices\Domain\Model\Invoice;
use Modules\Invoices\Domain\Model\InvoiceLine;

final readonly class InvoiceView
{
    public function __construct(
        private(set) array $data
    ) {}

    public static function fromInvoice(Invoice $invoice): self
    {
        return new self([
            'invoice_id' => (string) $invoice->id,
            'status' => $invoice->status,
            'customer_name' => $invoice->customer->name,
            'customer_email' => $invoice->customer->email,
            'lines' => array_map(
                static fn (InvoiceLine $line) => [
                    'product_name' => $line->productName,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unitPrice,
                    'total_price' => $line->totalPrice,
                ],
                $invoice->lines
            ),
            'total_price' => $invoice->totalPrice,
        ]);
    }
}
