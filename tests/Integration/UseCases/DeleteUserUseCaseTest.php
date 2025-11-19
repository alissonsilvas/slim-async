<?php

declare(strict_types=1);

namespace Tests\Integration\UseCases;

use App\Application\UseCases\User\DeleteUserUseCase;
use App\Domain\Entities\User;
use App\Domain\Enums\DocumentType;
use App\Domain\ValueObjects\Document;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Username;
use App\Infrastructure\Mongo\MongoConnection;
use App\Infrastructure\Repositories\MongoUserRepository;
use PHPUnit\Framework\TestCase;

class DeleteUserUseCaseTest extends TestCase
{
    private DeleteUserUseCase $useCase;
    private MongoUserRepository $repository;
    private MongoConnection $connection;

    protected function setUp(): void
    {
        $this->connection = new MongoConnection();
        $this->repository = new MongoUserRepository($this->connection);
        $this->useCase = new DeleteUserUseCase($this->repository);

        // Limpa a coleção antes de cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }

    public function testShouldDeleteExistingUser(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);

        $result = $this->useCase->execute($user->getId());

        $this->assertTrue($result);

        // Verifica se o usuário foi realmente deletado
        $deletedUser = $this->repository->findById($user->getId());
        $this->assertNull($deletedUser);
    }

    public function testShouldThrowExceptionForNonExistentUser(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('User not found');

        $this->useCase->execute('non-existent-id');
    }

    public function testShouldDeleteUserAndAllowRecreationWithSameData(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);
        $originalId = $user->getId();

        $result = $this->useCase->execute($user->getId());
        $this->assertTrue($result);

        // Cria novo usuário com os mesmos dados
        $newUser = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($newUser);

        // Verifica que é um novo usuário
        $this->assertNotEquals($originalId, $newUser->getId());

        $foundUser = $this->repository->findById($newUser->getId());
        $this->assertNotNull($foundUser);
        $this->assertEquals('johndoe', $foundUser->getUsername()->toString());
    }

    public function testShouldDeleteMultipleUsers(): void
    {
        $user1 = User::create(
            Username::fromString('user1'),
            Email::fromString('user1@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $user2 = User::create(
            Username::fromString('user2'),
            Email::fromString('user2@example.com'),
            Document::create(DocumentType::CPF, '52998224725')
        );

        $this->repository->save($user1);
        $this->repository->save($user2);

        $result1 = $this->useCase->execute($user1->getId());
        $result2 = $this->useCase->execute($user2->getId());

        $this->assertTrue($result1);
        $this->assertTrue($result2);

        $this->assertNull($this->repository->findById($user1->getId()));
        $this->assertNull($this->repository->findById($user2->getId()));
    }

    public function testShouldNotAffectOtherUsersWhenDeletingOne(): void
    {
        $user1 = User::create(
            Username::fromString('user1'),
            Email::fromString('user1@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $user2 = User::create(
            Username::fromString('user2'),
            Email::fromString('user2@example.com'),
            Document::create(DocumentType::CPF, '52998224725')
        );

        $this->repository->save($user1);
        $this->repository->save($user2);

        $this->useCase->execute($user1->getId());

        // Verifica que user1 foi deletado
        $this->assertNull($this->repository->findById($user1->getId()));

        // Verifica que user2 ainda existe
        $foundUser2 = $this->repository->findById($user2->getId());
        $this->assertNotNull($foundUser2);
        $this->assertEquals('user2', $foundUser2->getUsername()->toString());
    }

    protected function tearDown(): void
    {
        // Limpa a coleção após cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }
}

