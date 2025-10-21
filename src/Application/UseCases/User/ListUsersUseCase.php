<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\UserResponseDTO;
use App\Domain\Entities\User;
use App\Domain\Interfaces\UserRepositoryInterface;

final class ListUsersUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(int $page = 1, int $limit = 10): array
    {
        $users = $this->userRepository->findAll($page, $limit);

        return array_map(
            fn (User $user) => UserResponseDTO::fromEntity($user),
            $users
        );
    }
}
