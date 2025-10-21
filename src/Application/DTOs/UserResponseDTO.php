<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Entities\User;

final class UserResponseDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $typeDoc,
        public readonly string $numberDoc,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId(),
            username: $user->getUsername()->toString(),
            email: $user->getEmail()->toString(),
            typeDoc: $user->getDocument()->getType()->value,
            numberDoc: $user->getDocument()->getNumber(),
            createdAt: $user->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $user->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'type_doc' => $this->typeDoc,
            'number_doc' => $this->numberDoc,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
