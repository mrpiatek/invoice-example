<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Model;

use InvalidArgumentException;

final readonly class Customer
{
    public function __construct(
        private(set) string $name,
        private(set) string $email,
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException(
                'Customer name cannot be empty.'
            );
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException(
                'Customer email is invalid.'
            );
        }
    }
}
