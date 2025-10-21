<?php

declare(strict_types=1);

namespace App\Routes;

use App\Application\DTOs\CreateUserDTO;
use App\Application\DTOs\UpdateUserDTO;
use App\Application\Interfaces\UseCases\CreateUserUseCaseInterface;
use App\Application\Interfaces\UseCases\DeleteUserUseCaseInterface;
use App\Application\Interfaces\UseCases\GetUserUseCaseInterface;
use App\Application\Interfaces\UseCases\ListUsersUseCaseInterface;
use App\Application\Interfaces\UseCases\UpdateUserUseCaseInterface;
use App\Presentation\Controllers\UserController;
use App\Presentation\RouteFactory;
use Slim\App;

class UserRoutes
{
    public static function register(App $app): void
    {
        RouteFactory::createCrudRoutes(
            $app,
            '/users',
            UserController::class,
            CreateUserUseCaseInterface::class,
            GetUserUseCaseInterface::class,
            UpdateUserUseCaseInterface::class,
            DeleteUserUseCaseInterface::class,
            ListUsersUseCaseInterface::class,
            CreateUserDTO::class,
            UpdateUserDTO::class
        );
    }
}
