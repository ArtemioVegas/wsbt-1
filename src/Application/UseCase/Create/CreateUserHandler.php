<?php

declare(strict_types=1);

namespace App\Application\UseCase\Create;

use App\Application\Service\SequenceGeneratorInterface;
use App\Domain\Entity\User;
use App\Domain\Entity\UserRepositoryInterface;
use App\Domain\Exception\BannedWordException;
use App\Domain\Exception\UntrustedDomainException;
use App\Domain\Exception\UserInvalidEmailException;
use App\Domain\Exception\UserInvalidNameException;
use App\Domain\Exception\UserNotUniqueEmailException;
use App\Domain\Exception\UserNotUniqueNameException;
use App\Domain\Validator\BannedWordsValidatorInterface;
use App\Domain\Validator\UntrustedDomainValidatorInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;

class CreateUserHandler
{
    public function __construct(
        private SequenceGeneratorInterface        $sequenceGenerator,
        private UserRepositoryInterface           $userRepository,
        private BannedWordsValidatorInterface     $bannedWordsValidator,
        private UntrustedDomainValidatorInterface $untrustedDomainValidator,
    ) {
    }

    /**
     * @throws BannedWordException
     * @throws UserInvalidNameException
     * @throws UserNotUniqueEmailException
     * @throws UserNotUniqueNameException
     * @throws UserInvalidEmailException
     */
    public function __invoke(CreateUserData $data): void
    {
        if (!$this->bannedWordsValidator->validate($data->name)) {
            throw new BannedWordException('Banned words are not allowed. Passed value: ' . $data->name);
        }

        if (!$this->untrustedDomainValidator->validate($data->email)) {
            throw new UntrustedDomainException('Untrusted domains are not allowed. Passed value: ' . $data->email);
        }

        $user = new User(
            $this->sequenceGenerator->next(),
            new Name($data->name),
            new Email($data->email),
            $data->notes !== null ? new Note($data->notes) : null,
        );

        $this->userRepository->store($user);
    }
}
