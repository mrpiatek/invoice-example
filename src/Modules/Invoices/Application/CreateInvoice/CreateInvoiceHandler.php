<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\CreateInvoice;

use Modules\Invoices\Domain\Model\Customer;
use Modules\Invoices\Domain\Model\Invoice;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Invoices\Domain\Model\InvoiceProductLine;
use Modules\Invoices\Domain\Repository\InvoiceRepositoryInterface;

final readonly class CreateInvoiceHandler
{
    public function __construct(
        private InvoiceRepositoryInterface $invoices
    ) {}

    public function handle(CreateInvoiceCommand $command): InvoiceId
    {
        $id = $this->invoices->nextIdentity();

        $lines = array_map(
            static fn (array $line) => new InvoiceProductLine(
                productName: $line['product_name'],
                quantity: (int) $line['quantity'],
                unitPrice: (int) $line['unit_price']
            ),
            $command->lines
        );

        $invoice = Invoice::create(
            id: $id,
            customer: new Customer(
                $command->customerName,
                $command->customerEmail,
            ),
            lines: $lines,
        );

        $this->invoices->save($invoice);

        return $invoice->id;
    }
}
