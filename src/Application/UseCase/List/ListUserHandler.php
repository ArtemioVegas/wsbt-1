<?php

declare(strict_types=1);

namespace App\Application\UseCase\List;

use App\Domain\Entity\User;
use App\Domain\Entity\UserRepositoryInterface;

class ListUserHandler
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @return array<User>
     */
    public function __invoke(ListUserData $data): array
    {
        return $this->userRepository->list($data->limit, $data->offset);
    }
}
