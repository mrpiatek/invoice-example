<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Repository;

use Modules\Invoices\Domain\Model\Invoice;
use Modules\Invoices\Domain\Model\InvoiceId;

interface InvoiceRepositoryInterface
{
    public function nextIdentity(): InvoiceId;

    public function save(Invoice $invoice): void;

    public function get(InvoiceId $id): Invoice;

    public function find(InvoiceId $id): ?Invoice;
}
