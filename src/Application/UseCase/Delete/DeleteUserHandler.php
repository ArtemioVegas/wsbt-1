<?php

declare(strict_types=1);

namespace App\Application\UseCase\Delete;

use App\Application\Service\EventDispatcherInterface;
use App\Domain\Entity\UserRepositoryInterface;

class DeleteUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(DeleteUserData $data): void
    {
        $user = $this->userRepository->get($data->id);
        $user->delete();

        $events = $user->getEvents();
        if (!empty($events)) {
            $this->userRepository->remove($user);
            foreach ($events as $event) {
                $this->eventDispatcher->dispatch($event);
            }
            $user->clearEvents();
        }
    }
}
