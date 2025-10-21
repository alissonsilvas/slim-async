<?php

declare(strict_types=1);

namespace App\Application\Interfaces\UseCases;

interface DeleteUserUseCaseInterface
{
    public function execute(string $id): bool;
}
