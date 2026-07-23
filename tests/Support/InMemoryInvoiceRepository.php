<?php

declare(strict_types=1);

namespace Tests\Support;

use Modules\Invoices\Domain\Model\Invoice;
use Modules\Invoices\Domain\Model\InvoiceId;
use Modules\Invoices\Domain\Repository\InvoiceRepositoryInterface;
use Modules\Invoices\Infrastructure\Exception\InvoiceNotFoundException;

final class InMemoryInvoiceRepository implements InvoiceRepositoryInterface
{
    /** @var array<string, Invoice> */
    private array $invoices = [];

    public int $saveCount = 0;

    public function nextIdentity(): InvoiceId
    {
        return InvoiceId::generate();
    }

    public function save(Invoice $invoice): void
    {
        $this->invoices[(string) $invoice->id] = $invoice;
        $this->saveCount++;
    }

    public function get(InvoiceId $id): Invoice
    {
        return $this->invoices[(string) $id] ?? throw new InvoiceNotFoundException;
    }

    public function seed(Invoice $invoice): void
    {
        $this->invoices[(string) $invoice->id] = $invoice;
    }
}
