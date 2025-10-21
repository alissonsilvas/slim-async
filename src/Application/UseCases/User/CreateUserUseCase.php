<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\CreateUserDTO;
use App\Application\DTOs\UserResponseDTO;
use App\Application\Interfaces\UseCases\CreateUserUseCaseInterface;
use App\Domain\Entities\User;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Domain\ValueObjects\Document;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Username;

class CreateUserUseCase implements CreateUserUseCaseInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(CreateUserDTO $dto): UserResponseDTO
    {
        $this->validateUniqueConstraints($dto);

        $user = User::create(
            Username::fromString($dto->username),
            Email::fromString($dto->email),
            Document::create($dto->typeDoc, $dto->numberDoc)
        );

        $this->userRepository->save($user);

        return UserResponseDTO::fromEntity($user);
    }

    private function validateUniqueConstraints(CreateUserDTO $dto): void
    {
        if ($this->userRepository->existsByEmail($dto->email)) {
            throw new \DomainException('Email already exists');
        }

        if ($this->userRepository->existsByUsername($dto->username)) {
            throw new \DomainException('Username already exists');
        }
    }
}
