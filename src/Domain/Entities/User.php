<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Document;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Username;
use Ramsey\Uuid\Uuid;

final class User
{
    private function __construct(
        private string $id,
        private Username $username,
        private Email $email,
        private Document $document,
        private \DateTime $createdAt,
        private \DateTime $updatedAt
    ) {
    }

    public static function create(
        Username $username,
        Email $email,
        Document $document
    ): self {
        $now = new \DateTime();

        return new self(
            Uuid::uuid4()->toString(),
            $username,
            $email,
            $document,
            $now,
            $now
        );
    }

    public function update(
        ?Username $username = null,
        ?Email $email = null,
        ?Document $document = null
    ): void {
        if ($username !== null) {
            $this->username = $username;
        }

        if ($email !== null) {
            $this->email = $email;
        }

        if ($document !== null) {
            $this->document = $document;
        }

        $this->updatedAt = new \DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username->toString(),
            'email' => $this->email->toString(),
            'type_doc' => $this->document->getType()->value,
            'number_doc' => $this->document->getNumber(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
