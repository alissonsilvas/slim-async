<?php

declare(strict_types=1);

namespace App\Domain\Interfaces;

use App\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findByUsername(string $username): ?User;

    public function findAll(int $page = 1, int $limit = 10): array;

    public function delete(string $id): bool;

    public function existsByEmail(string $email): bool;

    public function existsByUsername(string $username): bool;
}
