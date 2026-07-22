<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Exception;

use Exception;
use Throwable;

final class InvoiceNotFoundException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }
}
