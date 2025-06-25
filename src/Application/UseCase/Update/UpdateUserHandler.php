<?php

declare(strict_types=1);

namespace App\Application\UseCase\Update;

use App\Application\Service\EventDispatcherInterface;
use App\Domain\Entity\UserRepositoryInterface;
use App\Domain\Exception\BannedWordException;
use App\Domain\Exception\UntrustedDomainException;
use App\Domain\Validator\BannedWordsValidatorInterface;
use App\Domain\Validator\UntrustedDomainValidatorInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;

class UpdateUserHandler
{
    public function __construct(
        private UserRepositoryInterface           $userRepository,
        private BannedWordsValidatorInterface     $bannedWordsValidator,
        private UntrustedDomainValidatorInterface $untrustedDomainValidator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(UpdateUserData $data): void
    {
        $user = $this->userRepository->get($data->id);

        if (!$this->bannedWordsValidator->validate($data->name)) {
            throw new BannedWordException('Banned words are not allowed. Passed value: ' . $data->name);
        }

        if (!$this->untrustedDomainValidator->validate($data->email)) {
            throw new UntrustedDomainException('Untrusted domains are not allowed. Passed value: ' . $data->email);
        }

        $user->changeName(new Name($data->name));
        $user->changeEmail(new Email($data->email));
        $user->changeNotes($data->notes !== null ? new Note($data->notes) : null);

        $events = $user->getEvents();
        if (!empty($events)) {
            $this->userRepository->store($user);
            foreach ($events as $event) {
                $this->eventDispatcher->dispatch($event);
            }
            $user->clearEvents();
        }
    }
}
