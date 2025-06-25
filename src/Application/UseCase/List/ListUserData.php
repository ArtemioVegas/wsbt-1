<?php

declare(strict_types=1);

namespace App\Application\UseCase\List;

class ListUserData
{
    public function __construct(
        public int $limit = 10,
        public int $offset = 0,
    ) {
    }
}
