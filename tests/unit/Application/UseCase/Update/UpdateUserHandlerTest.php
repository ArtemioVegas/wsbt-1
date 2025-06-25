<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\Update;

use App\Application\Service\EventDispatcherInterface;
use App\Application\UseCase\Update\UpdateUserData;
use App\Application\UseCase\Update\UpdateUserHandler;
use App\Domain\Entity\User;
use App\Domain\Entity\UserRepositoryInterface;
use App\Domain\Event\UserChangedEvent;
use App\Domain\Exception\BannedWordException;
use App\Domain\Exception\UntrustedDomainException;
use App\Domain\Validator\BannedWordsValidatorInterface;
use App\Domain\Validator\UntrustedDomainValidatorInterface;
use PHPUnit\Framework\TestCase;

final class UpdateUserHandlerTest extends TestCase
{
    public function testSuccessfulUpdateDispatchesEvents(): void
    {
        $id = 1;
        $name = 'validname';
        $email = 'user@example.com';
        $notes = 'Some note';
        $data = new UpdateUserData($id, $name, $email, $notes);


        $events = [
            new UserChangedEvent(1, 'name', 'oldname', 'validname'),
            new UserChangedEvent(1, 'email', 'old@example.com', 'user@example.com'),
        ];

        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('changeName');
        $user->expects($this->once())->method('changeEmail');
        $user->expects($this->once())->method('changeNotes');
        $user->expects($this->once())->method('getEvents')->willReturn($events);
        $user->expects($this->once())->method('clearEvents');

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())->method('get')->with(1)->willReturn($user);
        $userRepository->expects($this->once())->method('store')->with($user);

        $bannedValidator = $this->createMock(BannedWordsValidatorInterface::class);
        $bannedValidator->method('validate')->with($data->name)->willReturn(true);

        $domainValidator = $this->createMock(UntrustedDomainValidatorInterface::class);
        $domainValidator->method('validate')->with($data->email)->willReturn(true);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(self::exactly(count($events)))
            ->method('dispatch')
            ->willReturnCallback(function ($event) use ($events) {
                static $index = 0;
                $expected = $events[$index++];

                TestCase::assertInstanceOf(UserChangedEvent::class, $event);
                TestCase::assertSame($expected->id, $event->id);
                TestCase::assertSame($expected->fieldName, $event->fieldName);
                TestCase::assertSame($expected->oldValue, $event->oldValue);
                TestCase::assertSame($expected->newValue, $event->newValue);
            });

        $handler = new UpdateUserHandler($userRepository, $bannedValidator, $domainValidator, $dispatcher);
        $handler($data);
    }

    public function testThrowsOnBannedName(): void
    {
        $this->expectException(BannedWordException::class);

        $id = 2;
        $name = 'badword';
        $email = 'user@example.com';
        $notes = null;
        $data = new UpdateUserData($id, $name, $email, $notes);

        $bannedValidator = $this->createMock(BannedWordsValidatorInterface::class);
        $bannedValidator->method('validate')->with($data->name)->willReturn(false);

        $domainValidator = $this->createMock(UntrustedDomainValidatorInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $handler = new UpdateUserHandler($userRepository, $bannedValidator, $domainValidator, $dispatcher);
        $handler($data);
    }

    public function testThrowsOnUntrustedDomain(): void
    {
        $this->expectException(UntrustedDomainException::class);

        $id = 3;
        $name = 'validname';
        $email = 'spam@bad.com';
        $notes = null;
        $data = new UpdateUserData($id, $name, $email, $notes);

        $bannedValidator = $this->createMock(BannedWordsValidatorInterface::class);
        $bannedValidator->method('validate')->willReturn(true);

        $domainValidator = $this->createMock(UntrustedDomainValidatorInterface::class);
        $domainValidator->method('validate')->with($data->email)->willReturn(false);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $handler = new UpdateUserHandler($userRepository, $bannedValidator, $domainValidator, $dispatcher);
        $handler($data);
    }

    public function testNoEventsMeansNoDispatchOrStore(): void
    {
        $id = 4;
        $name = 'validname';
        $email = 'user@example.com';
        $notes = 'note';
        $data = new UpdateUserData($id, $name, $email, $notes);

        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('changeName');
        $user->expects($this->once())->method('changeEmail');
        $user->expects($this->once())->method('changeNotes');
        $user->expects($this->once())->method('getEvents')->willReturn([]);
        $user->expects($this->never())->method('clearEvents');

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('get')->with(4)->willReturn($user);
        $userRepository->expects($this->never())->method('store');

        $bannedValidator = $this->createMock(BannedWordsValidatorInterface::class);
        $bannedValidator->method('validate')->willReturn(true);

        $domainValidator = $this->createMock(UntrustedDomainValidatorInterface::class);
        $domainValidator->method('validate')->willReturn(true);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->never())->method('dispatch');

        $handler = new UpdateUserHandler($userRepository, $bannedValidator, $domainValidator, $dispatcher);
        $handler($data);
    }
}
