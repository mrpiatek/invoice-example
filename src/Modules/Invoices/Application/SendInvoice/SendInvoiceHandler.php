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
        $invoice = $this->invoices->get(
            $command->invoiceId
        );

        $invoice->send();

        $this->invoices->save($invoice);
    }
}
