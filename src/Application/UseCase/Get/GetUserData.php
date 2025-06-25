<?php

declare(strict_types=1);

namespace App\Application\UseCase\Get;

class GetUserData
{
    public function __construct(public int $id)
    {
    }
}
