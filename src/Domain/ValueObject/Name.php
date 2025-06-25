<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\UserInvalidNameException;

readonly class Name
{
    public string $value;

    /**
     * @throws UserInvalidNameException
     */
    public function __construct(string $value)
    {
        if (mb_strlen($value) < 8) {
            throw new UserInvalidNameException('The value cannot be shorter than 8 characters. Passed value is: ' . $value);
        }
        if (preg_match('/^[a-z0-9]+$/', $value) !== 1) {
            throw new UserInvalidNameException('The value can only consist of characters a-z and 0-9. Passed value is: ' . $value);
        }
        $this->value = $value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
