<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\UserInvalidEmailException;

readonly class Email
{
    public string $value;

    /**
     * @throws UserInvalidEmailException
     */
    public function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new UserInvalidEmailException('Invalid email. Passed value is: ' . $value);
        }

        $this->value = $value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
