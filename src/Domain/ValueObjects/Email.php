<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

final class Email
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $email): self
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }

        return new self($email);
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
