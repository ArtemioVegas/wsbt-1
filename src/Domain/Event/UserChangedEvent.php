<?php

declare(strict_types=1);

namespace App\Domain\Event;

class UserChangedEvent
{
    public function __construct(
        public int $id,
        public string $fieldName,
        public ?string $oldValue,
        public ?string $newValue
    ) {
    }
}
