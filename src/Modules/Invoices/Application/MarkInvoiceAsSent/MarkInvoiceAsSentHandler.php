<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\MarkInvoiceAsSent;

use Modules\Invoices\Domain\Repository\InvoiceRepositoryInterface;

final readonly class MarkInvoiceAsSentHandler
{
    public function __construct(
        private InvoiceRepositoryInterface $invoices,
    ) {}

    public function handle(MarkInvoiceAsSentCommand $command): void
    {
        $invoice = $this->invoices->get(
            $command->invoiceId
        );

        $invoice->markAsSentToClient();

        $this->invoices->save($invoice);
    }
}
