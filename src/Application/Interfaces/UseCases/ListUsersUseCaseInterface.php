<?php

declare(strict_types=1);

namespace App\Application\Interfaces\UseCases;

use App\Application\DTOs\UserResponseDTO;

interface ListUsersUseCaseInterface
{
    /**
     * @return UserResponseDTO[]
     */
    public function execute(int $page = 1, int $limit = 10): array;
}
