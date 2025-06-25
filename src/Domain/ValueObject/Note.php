<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

readonly class Note
{
    public function __construct(public string $value)
    {
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
