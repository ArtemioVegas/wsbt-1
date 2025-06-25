<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\Get;

use App\Application\UseCase\Get\GetUserData;
use App\Application\UseCase\Get\GetUserHandler;
use App\Domain\Entity\User;
use App\Domain\Entity\UserRepositoryInterface;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Name;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetUserHandlerTest extends TestCase
{
    private MockObject $userRepositoryMock;
    private GetUserHandler $getUserHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $this->getUserHandler = new GetUserHandler($this->userRepositoryMock);
    }

    public function testUserFound(): void
    {
        $userId = 1;
        $user = new User($userId, new Name('user1234'), new Email('test@mail.com'));
        $getUserData = new GetUserData($userId);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($userId))
            ->willReturn($user);

        $result = ($this->getUserHandler)($getUserData);

        $this->assertSame($user, $result);
    }

    public function testUserNotFound(): void
    {
        $userId = 999;
        $getUserData = new GetUserData($userId);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($userId))
            ->willThrowException(new UserNotFoundException());

        $this->expectException(UserNotFoundException::class);

        ($this->getUserHandler)($getUserData);
    }
}
