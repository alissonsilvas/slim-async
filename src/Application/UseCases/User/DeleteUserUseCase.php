<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Domain\Interfaces\UserRepositoryInterface;

final class DeleteUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(string $id): bool
    {
        $user = $this->userRepository->findById($id);

        if ($user === null) {
            throw new \DomainException('User not found');
        }

        return $this->userRepository->delete($id);
    }
}
