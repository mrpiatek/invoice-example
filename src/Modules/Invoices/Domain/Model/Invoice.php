<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Model;

use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exception\InvalidInvoiceOperationException;
use Modules\Invoices\Domain\Exception\InvalidInvoiceStatusTransitionException;

final class Invoice
{
    public int $totalPrice {
        get => (int) array_reduce(
            $this->lines,
            static fn (?int $carry, InvoiceLine $line) => $carry + $line->totalPrice
        );
    }

    /** @param InvoiceLine[] $lines */
    private function __construct(
        private(set) InvoiceId $id,
        private(set) StatusEnum $status,
        private(set) Customer $customer,
        private(set) array $lines = [],
    ) {}

    /**
     * @param  InvoiceLine[]  $lines
     */
    public static function create(
        InvoiceId $id,
        Customer $customer,
        array $lines = [],
    ): self {
        $invoice = new self(
            id: $id,
            status: StatusEnum::Draft,
            customer: $customer,
        );

        foreach ($lines as $line) {
            $invoice->addLine($line);
        }

        return $invoice;
    }

    public function addLine(InvoiceLine $line): void
    {
        if ($this->status !== StatusEnum::Draft) {
            throw new InvalidInvoiceOperationException(
                'Lines can only be modified while the invoice is in draft status.'
            );
        }

        $this->lines[] = $line;
    }

    public function send(): void
    {
        if ($this->status !== StatusEnum::Draft) {
            throw new InvalidInvoiceStatusTransitionException(
                'Only a draft invoice can be sent.'
            );
        }

        if ($this->lines === []) {
            throw new InvalidInvoiceStatusTransitionException(
                'An invoice must contain at least one product line.'
            );
        }

        foreach ($this->lines as $line) {
            if ($line->isSendable === false) {
                throw new InvalidInvoiceOperationException(
                    'Every line must have a positive quantity and unit price.'
                );
            }
        }

        $this->status = StatusEnum::Sending;
    }

    public function markAsSentToClient(): void
    {
        if ($this->status !== StatusEnum::Sending) {
            throw new InvalidInvoiceOperationException(
                'Only an invoice in sending status can be marked as sent.'
            );
        }

        $this->status = StatusEnum::SentToClient;
    }
}
