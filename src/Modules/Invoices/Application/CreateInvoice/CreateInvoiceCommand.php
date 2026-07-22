<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\CreateInvoice;

final readonly class CreateInvoiceCommand
{
    public function __construct(
        public string $customerName,
        public string $customerEmail,
        public array $lines,
    ) {}
}
