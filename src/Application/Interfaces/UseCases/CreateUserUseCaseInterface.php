<?php

declare(strict_types=1);

namespace App\Application\Interfaces\UseCases;

use App\Application\DTOs\CreateUserDTO;
use App\Application\DTOs\UserResponseDTO;

interface CreateUserUseCaseInterface
{
    public function execute(CreateUserDTO $dto): UserResponseDTO;
}
