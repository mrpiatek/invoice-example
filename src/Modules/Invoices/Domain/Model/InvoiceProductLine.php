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

    public function __construct(
        private(set) string $productName,
        private(set) int $quantity,
        private(set) int $unitPrice,
    ) {
        if (trim($productName) === '') {
            throw new InvalidArgumentException(
                'Product name cannot be empty.'
            );
        }
    }
}
