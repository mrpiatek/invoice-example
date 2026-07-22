<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\GetInvoice;

use Modules\Invoices\Domain\Repository\InvoiceRepositoryInterface;
use Modules\Invoices\Presentation\View\InvoiceView;

final readonly class GetInvoiceHandler
{
    public function __construct(
        private InvoiceRepositoryInterface $invoices
    ) {}

    public function handle(GetInvoiceQuery $query): InvoiceView
    {
        $invoice = $this->invoices->get(
            $query->invoiceId
        );

        return InvoiceView::fromInvoice($invoice);
    }
}
