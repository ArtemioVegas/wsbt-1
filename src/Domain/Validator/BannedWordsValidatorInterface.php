<?php

declare(strict_types=1);

namespace App\Domain\Validator;

interface BannedWordsValidatorInterface
{
    public function validate(string $word): bool;
}
