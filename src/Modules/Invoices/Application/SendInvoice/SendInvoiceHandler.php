<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\SendInvoice;

use Modules\Invoices\Domain\Repository\InvoiceRepositoryInterface;

final readonly class SendInvoiceHandler
{
    public function __construct(
        private InvoiceRepositoryInterface $invoices
    ) {}

    public function handle(SendInvoiceCommand $command): void
    {
        $this->invoices->get(
            $command->invoiceId
        )->send();
    }
}
