<?php

declare(strict_types=1);

namespace App\Application\UseCase\Create;

class CreateUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $notes = null,
    ) {
    }
}
