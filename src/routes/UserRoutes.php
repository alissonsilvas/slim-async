<?php

declare(strict_types=1);

namespace App\Routes;

use App\Application\DTOs\CreateUserDTO;
use App\Application\DTOs\UpdateUserDTO;
use App\Application\UseCases\User\CreateUserUseCase;
use App\Application\UseCases\User\DeleteUserUseCase;
use App\Application\UseCases\User\GetUserUseCase;
use App\Application\UseCases\User\ListUsersUseCase;
use App\Application\UseCases\User\UpdateUserUseCase;
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
            CreateUserUseCase::class,
            GetUserUseCase::class,
            UpdateUserUseCase::class,
            DeleteUserUseCase::class,
            ListUsersUseCase::class,
            CreateUserDTO::class,
            UpdateUserDTO::class
        );
    }
}
