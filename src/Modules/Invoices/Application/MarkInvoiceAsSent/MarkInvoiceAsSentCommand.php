<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\MarkInvoiceAsSent;

use Modules\Invoices\Domain\Model\InvoiceId;

final readonly class MarkInvoiceAsSentCommand
{
    public function __construct(
        public InvoiceId $invoiceId
    ) {}
}
