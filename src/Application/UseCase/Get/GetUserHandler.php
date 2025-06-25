<?php

declare(strict_types=1);

namespace App\Application\UseCase\Get;

use App\Domain\Entity\User;
use App\Domain\Entity\UserRepositoryInterface;
use App\Domain\Exception\UserNotFoundException;

class GetUserHandler
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @throws UserNotFoundException
     */
    public function __invoke(GetUserData $data): User
    {
        return $this->userRepository->get($data->id);
    }
}
