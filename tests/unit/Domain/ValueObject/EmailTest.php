<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\UserInvalidEmailException;
use App\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testValidEmailCreatesSuccessfully(): void
    {
        $email = new Email('user@example.com');
        $this->assertSame('user@example.com', $email->value);
    }

    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(UserInvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email. Passed value is: not-an-email');

        new Email('not-an-email');
    }

    public function testEqualsReturnsTrueForSameEmail(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');

        $this->assertTrue($email1->equals($email2));
    }

    public function testEqualsReturnsFalseForDifferentEmail(): void
    {
        $email1 = new Email('first@example.com');
        $email2 = new Email('second@example.com');

        $this->assertFalse($email1->equals($email2));
    }
}
