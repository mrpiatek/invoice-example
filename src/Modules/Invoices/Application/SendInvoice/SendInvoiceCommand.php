<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\SendInvoice;

use Modules\Invoices\Domain\Model\InvoiceId;

final readonly class SendInvoiceCommand
{
    public function __construct(
        public InvoiceId $invoiceId
    ) {}
}
