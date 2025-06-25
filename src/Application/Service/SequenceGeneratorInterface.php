<?php

declare(strict_types=1);

namespace App\Application\Service;

interface SequenceGeneratorInterface
{
    public function next(): int;
}
