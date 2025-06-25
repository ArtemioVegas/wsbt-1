<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\List;

use App\Application\UseCase\List\ListUserData;
use App\Application\UseCase\List\ListUserHandler;
use App\Domain\Entity\User;
use App\Domain\Entity\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ListUserHandlerTest extends TestCase
{
    private MockObject $userRepositoryMock;
    private ListUserHandler $listUserHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $this->listUserHandler = new ListUserHandler($this->userRepositoryMock);
    }

    public function testUsersFound(): void
    {
        $limit = 10;
        $offset = 0;
        $userData = new ListUserData($limit, $offset);

        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('list')
            ->with($limit, $offset)
            ->willReturn([$user1, $user2]);

        $result = $this->listUserHandler->__invoke($userData);

        $this->assertCount(2, $result);
        $this->assertSame($user1, $result[0]);
        $this->assertSame($user2, $result[1]);
    }

    public function testNoUsersFound(): void
    {
        $limit = 10;
        $offset = 0;
        $userData = new ListUserData($limit, $offset);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('list')
            ->with($limit, $offset)
            ->willReturn([]);

        $result = $this->listUserHandler->__invoke($userData);

        $this->assertEmpty($result);
    }
}
