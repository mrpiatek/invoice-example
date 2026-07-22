<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\GetInvoice;

use Modules\Invoices\Domain\Model\InvoiceId;

final readonly class GetInvoiceQuery
{
    public function __construct(
        public InvoiceId $invoiceId
    ) {}
}
