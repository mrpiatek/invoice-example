<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Model;

use Override;
use Ramsey\Uuid\Uuid;
use Stringable;

final class InvoiceProductLineId implements Stringable
{
    public function __construct(
        private string $invoiceProductLineId
    ) {}

    public static function generate(): self
    {
        return new self(Uuid::uuid7()->toString());
    }

    #[Override]
    public function __toString(): string
    {
        return $this->invoiceProductLineId;
    }
}
