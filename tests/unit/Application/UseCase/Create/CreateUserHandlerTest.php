<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\UseCase\Create;

use App\Application\Service\SequenceGeneratorInterface;
use App\Application\UseCase\Create\CreateUserData;
use App\Application\UseCase\Create\CreateUserHandler;
use App\Domain\Entity\User;
use App\Domain\Entity\UserRepositoryInterface;
use App\Domain\Exception\BannedWordException;
use App\Domain\Exception\UntrustedDomainException;
use App\Domain\Validator\BannedWordsValidatorInterface;
use App\Domain\Validator\UntrustedDomainValidatorInterface;
use PHPUnit\Framework\TestCase;

final class CreateUserHandlerTest extends TestCase
{
    public function testItSuccessfullyCreatesUser(): void
    {
        $name = 'gohnatas';
        $email = 'john@example.com';
        $notes = 'Test note';
        $data = new CreateUserData($name, $email, $notes);

        $sequenceGenerator = $this->createMock(SequenceGeneratorInterface::class);
        $sequenceGenerator->method('next')->willReturn(1);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())
            ->method('store')
            ->with($this->callback(function (User $user) use ($data) {
                return $user->getId() === 1
                    && $user->getName()->value === $data->name
                    && $user->getEmail()->value === $data->email
                    && $user->getNotes()?->value === $data->notes;
            }));

        $bannedValidator = $this->createMock(BannedWordsValidatorInterface::class);
        $bannedValidator->method('validate')->with($data->name)->willReturn(true);

        $domainValidator = $this->createMock(UntrustedDomainValidatorInterface::class);
        $domainValidator->method('validate')->with($data->email)->willReturn(true);

        $handler = new CreateUserHandler($sequenceGenerator, $userRepository, $bannedValidator, $domainValidator);
        $handler($data);
    }

    public function testItThrowsOnBannedWord(): void
    {
        $this->expectException(BannedWordException::class);

        $name = 'badworddd';
        $email = 'user@example.com';
        $notes = null;
        $data = new CreateUserData($name, $email, $notes);


        $sequenceGenerator = $this->createMock(SequenceGeneratorInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);

        $bannedValidator = $this->createMock(BannedWordsValidatorInterface::class);
        $bannedValidator->method('validate')->with($data->name)->willReturn(false);

        $domainValidator = $this->createMock(UntrustedDomainValidatorInterface::class);
        $domainValidator->expects($this->never())->method('validate');

        $handler = new CreateUserHandler($sequenceGenerator, $userRepository, $bannedValidator, $domainValidator);
        $handler($data);
    }

    public function testItThrowsOnUntrustedDomain(): void
    {
        $this->expectException(UntrustedDomainException::class);

        $name = 'gohnatas';
        $email = 'user@spam.com';
        $notes = null;
        $data = new CreateUserData($name, $email, $notes);


        $sequenceGenerator = $this->createMock(SequenceGeneratorInterface::class);
        $userRepository = $this->createMock(UserRepositoryInterface::class);

        $bannedValidator = $this->createMock(BannedWordsValidatorInterface::class);
        $bannedValidator->method('validate')->willReturn(true);

        $domainValidator = $this->createMock(UntrustedDomainValidatorInterface::class);
        $domainValidator->method('validate')->with($data->email)->willReturn(false);

        $handler = new CreateUserHandler($sequenceGenerator, $userRepository, $bannedValidator, $domainValidator);
        $handler($data);
    }
}
