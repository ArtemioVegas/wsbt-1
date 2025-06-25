<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\UserNotFoundException;
use App\Domain\Exception\UserNotUniqueEmailException;
use App\Domain\Exception\UserNotUniqueNameException;

interface UserRepositoryInterface
{
    /**
     * @throws UserNotFoundException
     */
    public function get(int $id): User;

    /**
     * @return array<User>
     */
    public function list(int $limit, int $offset): array;

    /**
     * @throws UserNotUniqueNameException
     * @throws UserNotUniqueEmailException
     */
    public function store(User $user): void;

    public function remove(User $user): void;
}
