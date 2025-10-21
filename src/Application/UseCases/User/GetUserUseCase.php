<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\UserResponseDTO;
use App\Domain\Interfaces\UserRepositoryInterface;

final class GetUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(string $id): UserResponseDTO
    {
        $user = $this->userRepository->findById($id);

        if ($user === null) {
            throw new \DomainException('User not found');
        }

        return UserResponseDTO::fromEntity($user);
    }
}
