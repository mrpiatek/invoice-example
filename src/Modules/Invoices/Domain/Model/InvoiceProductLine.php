<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Model;

use InvalidArgumentException;

final class InvoiceProductLine
{
    public int $totalPrice {
        get => $this->unitPrice * $this->quantity;

    }

    public bool $isSendable {
        get => $this->quantity > 0 && $this->unitPrice > 0;
    }

    private function __construct(
        private(set) InvoiceProductLineId $id,
        private(set) InvoiceId $invoiceId,
        private(set) string $productName,
        private(set) int $quantity,
        private(set) int $unitPrice,
    ) {}

    public static function create(
        InvoiceProductLineId $id,
        InvoiceId $invoiceId,
        string $productName,
        int $quantity,
        int $unitPrice,
    ): self {
        if (empty(trim($productName))) {
            throw new InvalidArgumentException(
                'Product name cannot be empty.'
            );
        }

        return new self($id, $invoiceId, $productName, $quantity, $unitPrice);
    }

    public static function reconstitute(
        InvoiceProductLineId $id,
        InvoiceId $invoiceId,
        string $productName,
        int $quantity,
        int $unitPrice,
    ): self {
        return new self($id, $invoiceId, $productName, $quantity, $unitPrice);
    }
}
