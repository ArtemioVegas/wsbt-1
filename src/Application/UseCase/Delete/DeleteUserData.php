<?php

declare(strict_types=1);

namespace App\Application\UseCase\Delete;

class DeleteUserData
{
    public function __construct(
        public int $id,
    ) {
    }
}
