<?php

declare(strict_types=1);

namespace Tests\Integration\UseCases;

use App\Application\UseCases\User\GetUserUseCase;
use App\Domain\Entities\User;
use App\Domain\Enums\DocumentType;
use App\Domain\ValueObjects\Document;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Username;
use App\Infrastructure\Mongo\MongoConnection;
use App\Infrastructure\Repositories\MongoUserRepository;
use PHPUnit\Framework\TestCase;

class GetUserUseCaseTest extends TestCase
{
    private GetUserUseCase $useCase;
    private MongoUserRepository $repository;
    private MongoConnection $connection;

    protected function setUp(): void
    {
        $this->connection = new MongoConnection();
        $this->repository = new MongoUserRepository($this->connection);
        $this->useCase = new GetUserUseCase($this->repository);

        // Limpa a coleção antes de cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }

    public function testShouldGetUserById(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);

        $result = $this->useCase->execute($user->getId());

        $this->assertNotNull($result);
        $this->assertEquals($user->getId(), $result->id);
        $this->assertEquals('johndoe', $result->username);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertEquals('CPF', $result->typeDoc);
        $this->assertEquals('11144477735', $result->numberDoc);
    }

    public function testShouldThrowExceptionForNonExistentUser(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('User not found');

        $this->useCase->execute('non-existent-id');
    }

    public function testShouldGetUserWithCNPJ(): void
    {
        $user = User::create(
            Username::fromString('companyxyz'),
            Email::fromString('contact@company.com'),
            Document::create(DocumentType::CNPJ, '11222333000181')
        );

        $this->repository->save($user);

        $result = $this->useCase->execute($user->getId());

        $this->assertEquals('CNPJ', $result->typeDoc);
        $this->assertEquals('11222333000181', $result->numberDoc);
    }

    public function testShouldGetMultipleDifferentUsers(): void
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

        $this->assertEquals('user1', $result1->username);
        $this->assertEquals('user2', $result2->username);
        $this->assertNotEquals($result1->id, $result2->id);
    }

    protected function tearDown(): void
    {
        // Limpa a coleção após cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }
}

