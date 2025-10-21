<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

final class Username
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $username): self
    {
        if (strlen($username) < 3 || strlen($username) > 50) {
            throw new \InvalidArgumentException('Username must be between 3 and 50 characters');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            throw new \InvalidArgumentException('Username can only contain letters, numbers and underscores');
        }

        return new self($username);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
