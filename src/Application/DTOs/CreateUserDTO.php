<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\DocumentType;

final class CreateUserDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly DocumentType $typeDoc,
        public readonly string $numberDoc
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            username: $data['username'],
            email: $data['email'],
            typeDoc: DocumentType::from($data['type_doc']),
            numberDoc: $data['number_doc']
        );
    }

    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'type_doc' => $this->typeDoc->value,
            'number_doc' => $this->numberDoc,
        ];
    }
}
