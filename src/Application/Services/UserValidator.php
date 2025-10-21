<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\CreateUserDTO;
use App\Application\DTOs\UpdateUserDTO;
use App\Domain\Enums\DocumentType;

final class UserValidator
{
    public function validateCreate(CreateUserDTO $dto): void
    {
        $this->validateUsername($dto->username);
        $this->validateEmail($dto->email);
        $this->validateDocument($dto->typeDoc, $dto->numberDoc);
    }

    public function validateUpdate(UpdateUserDTO $dto): void
    {
        if ($dto->username !== null) {
            $this->validateUsername($dto->username);
        }

        if ($dto->email !== null) {
            $this->validateEmail($dto->email);
        }

        if ($dto->typeDoc !== null && $dto->numberDoc !== null) {
            $this->validateDocument($dto->typeDoc, $dto->numberDoc);
        }
    }

    private function validateUsername(string $username): void
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('Username is required');
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            throw new \InvalidArgumentException('Username must be between 3 and 50 characters');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            throw new \InvalidArgumentException('Username can only contain letters, numbers and underscores');
        }
    }

    private function validateEmail(string $email): void
    {
        if (empty($email)) {
            throw new \InvalidArgumentException('Email is required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }

    private function validateDocument(DocumentType $type, string $number): void
    {
        if (empty($number)) {
            throw new \InvalidArgumentException('Document number is required');
        }

        $cleanNumber = preg_replace('/\D/', '', $number);

        if (strlen($cleanNumber) !== $type->getLength()) {
            throw new \InvalidArgumentException(
                sprintf('%s must have %d digits', $type->value, $type->getLength())
            );
        }
    }
}
