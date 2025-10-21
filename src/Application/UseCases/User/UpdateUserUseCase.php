<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\UpdateUserDTO;
use App\Application\DTOs\UserResponseDTO;
use App\Application\Interfaces\UseCases\UpdateUserUseCaseInterface;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Domain\ValueObjects\Document;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Username;

class UpdateUserUseCase implements UpdateUserUseCaseInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(string $id, UpdateUserDTO $dto): UserResponseDTO
    {
        $user = $this->userRepository->findById($id);

        if ($user === null) {
            throw new \DomainException('User not found');
        }

        if (!$dto->hasUpdates()) {
            return UserResponseDTO::fromEntity($user);
        }

        $this->validateUniqueConstraints($dto, $id);

        $username = $dto->username ? Username::fromString($dto->username) : null;
        $email = $dto->email ? Email::fromString($dto->email) : null;
        $document = ($dto->typeDoc && $dto->numberDoc)
            ? Document::create($dto->typeDoc, $dto->numberDoc)
            : null;

        $user->update($username, $email, $document);

        $this->userRepository->save($user);

        return UserResponseDTO::fromEntity($user);
    }

    private function validateUniqueConstraints(UpdateUserDTO $dto, string $currentUserId): void
    {
        if ($dto->email !== null) {
            $existingUser = $this->userRepository->findByEmail($dto->email);
            if ($existingUser !== null && $existingUser->getId() !== $currentUserId) {
                throw new \DomainException('Email already exists');
            }
        }

        if ($dto->username !== null) {
            $existingUser = $this->userRepository->findByUsername($dto->username);
            if ($existingUser !== null && $existingUser->getId() !== $currentUserId) {
                throw new \DomainException('Username already exists');
            }
        }
    }
}
