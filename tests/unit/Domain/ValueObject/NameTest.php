<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\UserInvalidNameException;
use App\Domain\ValueObject\Name;
use PHPUnit\Framework\TestCase;

final class NameTest extends TestCase
{
    public function testValidNameCreatesSuccessfully(): void
    {
        $name = new Name('valid123');
        $this->assertSame('valid123', $name->value);
    }

    public function testNameTooShortThrowsException(): void
    {
        $this->expectException(UserInvalidNameException::class);
        $this->expectExceptionMessage('The value cannot be shorter than 8 characters. Passed value is: short');
        new Name('short');
    }

    public function testNameWithInvalidCharactersThrowsException(): void
    {
        $this->expectException(UserInvalidNameException::class);
        $this->expectExceptionMessage('The value can only consist of characters a-z and 0-9. Passed value is: invalid@#');
        new Name('invalid@#');
    }

    public function testEqualsReturnsTrueForSameValues(): void
    {
        $name1 = new Name('username1');
        $name2 = new Name('username1');

        $this->assertTrue($name1->equals($name2));
    }

    public function testEqualsReturnsFalseForDifferentValues(): void
    {
        $name1 = new Name('username1');
        $name2 = new Name('username2');

        $this->assertFalse($name1->equals($name2));
    }
}
