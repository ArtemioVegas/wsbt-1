<?php

declare(strict_types=1);

namespace App\Application\UseCase\Update;

class UpdateUserData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $notes
    ) {
    }
}
