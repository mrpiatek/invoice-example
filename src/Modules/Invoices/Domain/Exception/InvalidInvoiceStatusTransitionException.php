<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exception;

use Exception;
use Throwable;

final class InvalidInvoiceStatusTransitionException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
