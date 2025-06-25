<?php

declare(strict_types=1);

namespace App\Domain\Validator;

interface UntrustedDomainValidatorInterface
{
    public function validate(string $domain): bool;
}
