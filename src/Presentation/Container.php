<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Application\UseCases\User\CreateUserUseCase;
use App\Application\UseCases\User\DeleteUserUseCase;
use App\Application\UseCases\User\GetUserUseCase;
use App\Application\UseCases\User\ListUsersUseCase;
use App\Application\UseCases\User\UpdateUserUseCase;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Infrastructure\Mongo\MongoConnection;
use App\Infrastructure\Repositories\MongoUserRepository;

class Container
{
    private static ?Container $instance = null;
    private array $services = [];

    private function __construct()
    {
        $this->registerServices();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function registerServices(): void
    {
        // Infrastructure
        $this->services[MongoConnection::class] = fn () => new MongoConnection();
        $this->services[UserRepositoryInterface::class] = fn () => new MongoUserRepository(
            $this->get(MongoConnection::class)
        );

        // Use Cases
        $this->services[CreateUserUseCase::class] = fn () => new CreateUserUseCase(
            $this->get(UserRepositoryInterface::class)
        );

        $this->services[GetUserUseCase::class] = fn () => new GetUserUseCase(
            $this->get(UserRepositoryInterface::class)
        );

        $this->services[UpdateUserUseCase::class] = fn () => new UpdateUserUseCase(
            $this->get(UserRepositoryInterface::class)
        );

        $this->services[DeleteUserUseCase::class] = fn () => new DeleteUserUseCase(
            $this->get(UserRepositoryInterface::class)
        );

        $this->services[ListUsersUseCase::class] = fn () => new ListUsersUseCase(
            $this->get(UserRepositoryInterface::class)
        );
    }

    public function get(string $className): object
    {
        if (!isset($this->services[$className])) {
            throw new \InvalidArgumentException("Service {$className} not found");
        }

        if (is_callable($this->services[$className])) {
            $this->services[$className] = $this->services[$className]();
        }

        return $this->services[$className];
    }

    public function register(string $className, callable $factory): void
    {
        $this->services[$className] = $factory;
    }
}
