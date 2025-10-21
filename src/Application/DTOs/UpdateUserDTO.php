<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\DocumentType;

final class UpdateUserDTO
{
    public function __construct(
        public readonly ?string $username = null,
        public readonly ?string $email = null,
        public readonly ?DocumentType $typeDoc = null,
        public readonly ?string $numberDoc = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            username: $data['username'] ?? null,
            email: $data['email'] ?? null,
            typeDoc: isset($data['type_doc']) ? DocumentType::from($data['type_doc']) : null,
            numberDoc: $data['number_doc'] ?? null
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->username !== null) {
            $data['username'] = $this->username;
        }

        if ($this->email !== null) {
            $data['email'] = $this->email;
        }

        if ($this->typeDoc !== null) {
            $data['type_doc'] = $this->typeDoc->value;
        }

        if ($this->numberDoc !== null) {
            $data['number_doc'] = $this->numberDoc;
        }

        return $data;
    }

    public function hasUpdates(): bool
    {
        return $this->username !== null
            || $this->email !== null
            || $this->typeDoc !== null
            || $this->numberDoc !== null;
    }
}
