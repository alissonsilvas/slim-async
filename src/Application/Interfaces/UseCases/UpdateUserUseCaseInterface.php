<?php

declare(strict_types=1);

namespace App\Application\Interfaces\UseCases;

use App\Application\DTOs\UpdateUserDTO;
use App\Application\DTOs\UserResponseDTO;

interface UpdateUserUseCaseInterface
{
    public function execute(string $id, UpdateUserDTO $dto): UserResponseDTO;
}
