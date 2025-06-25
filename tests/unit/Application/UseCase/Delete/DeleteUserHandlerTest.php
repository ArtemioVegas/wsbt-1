<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\Delete;

use App\Application\Service\EventDispatcherInterface;
use App\Application\UseCase\Delete\DeleteUserData;
use App\Application\UseCase\Delete\DeleteUserHandler;
use App\Domain\Entity\User;
use App\Domain\Entity\UserRepositoryInterface;
use App\Domain\Event\UserChangedEvent;
use PHPUnit\Framework\TestCase;

final class DeleteUserHandlerTest extends TestCase
{
    public function testUserDeletedSuccessfullyAndEventDispatched(): void
    {
        $data = new DeleteUserData(42);

        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('delete');
        $user->expects($this->once())->method('getEvents')->willReturn([new UserChangedEvent(42, 'deleted', null, '2024-01-01 15:20:30')]);
        $user->expects($this->once())->method('clearEvents');

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())->method('get')->with(42)->willReturn($user);
        $userRepository->expects($this->once())->method('remove')->with($user);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) {
                return $event instanceof UserChangedEvent
                    && $event->fieldName === 'deleted'
                    && $event->id === 42
                    && $event->oldValue === null
                    && $event->newValue === '2024-01-01 15:20:30';
            }));

        $handler = new DeleteUserHandler($userRepository, $dispatcher);
        $handler($data);
    }

    public function testUserDeletedButNoChanges(): void
    {
        $data = new DeleteUserData(99);

        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('delete');
        $user->expects($this->once())->method('getEvents')->willReturn([]);
        $user->expects($this->never())->method('clearEvents');

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())->method('get')->with(99)->willReturn($user);
        $userRepository->expects($this->never())->method('remove');

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())->method('dispatch');

        $handler = new DeleteUserHandler($userRepository, $dispatcher);
        $handler($data);
    }
}
