<?php

declare(strict_types=1);

namespace Tests\Integration\UseCases;

use App\Application\DTOs\CreateUserDTO;
use App\Application\UseCases\User\CreateUserUseCase;
use App\Domain\Enums\DocumentType;
use App\Infrastructure\Mongo\MongoConnection;
use App\Infrastructure\Repositories\MongoUserRepository;
use PHPUnit\Framework\TestCase;

class CreateUserUseCaseTest extends TestCase
{
    private CreateUserUseCase $useCase;
    private MongoUserRepository $repository;
    private MongoConnection $connection;

    protected function setUp(): void
    {
        $this->connection = new MongoConnection();
        $this->repository = new MongoUserRepository($this->connection);
        $this->useCase = new CreateUserUseCase($this->repository);

        // Limpa a coleção antes de cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }

    public function testShouldCreateUserWithValidCPF(): void
    {
        $dto = new CreateUserDTO(
            username: 'johndoe',
            email: 'john@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '11144477735'
        );

        $result = $this->useCase->execute($dto);

        $this->assertNotNull($result);
        $this->assertEquals('johndoe', $result->username);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertEquals('CPF', $result->typeDoc);
        $this->assertEquals('11144477735', $result->numberDoc);
        $this->assertNotEmpty($result->id);
        $this->assertNotEmpty($result->createdAt);
        $this->assertNotEmpty($result->updatedAt);
    }

    public function testShouldCreateUserWithValidCNPJ(): void
    {
        $dto = new CreateUserDTO(
            username: 'companyxyz',
            email: 'contact@company.com',
            typeDoc: DocumentType::CNPJ,
            numberDoc: '11222333000181'
        );

        $result = $this->useCase->execute($dto);

        $this->assertNotNull($result);
        $this->assertEquals('companyxyz', $result->username);
        $this->assertEquals('contact@company.com', $result->email);
        $this->assertEquals('CNPJ', $result->typeDoc);
        $this->assertEquals('11222333000181', $result->numberDoc);
    }

    public function testShouldThrowExceptionForDuplicateEmail(): void
    {
        $dto1 = new CreateUserDTO(
            username: 'user1',
            email: 'duplicate@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '11144477735'
        );

        $this->useCase->execute($dto1);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Email already exists');

        $dto2 = new CreateUserDTO(
            username: 'user2',
            email: 'duplicate@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '22233344455'
        );

        $this->useCase->execute($dto2);
    }

    public function testShouldThrowExceptionForDuplicateUsername(): void
    {
        $dto1 = new CreateUserDTO(
            username: 'duplicateuser',
            email: 'user1@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '11144477735'
        );

        $this->useCase->execute($dto1);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Username already exists');

        $dto2 = new CreateUserDTO(
            username: 'duplicateuser',
            email: 'user2@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '22233344455'
        );

        $this->useCase->execute($dto2);
    }

    public function testShouldThrowExceptionForInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        $dto = new CreateUserDTO(
            username: 'johndoe',
            email: 'invalid-email',
            typeDoc: DocumentType::CPF,
            numberDoc: '11144477735'
        );

        $this->useCase->execute($dto);
    }

    public function testShouldThrowExceptionForInvalidUsername(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Username must be between 3 and 50 characters');

        $dto = new CreateUserDTO(
            username: 'ab',
            email: 'john@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '11144477735'
        );

        $this->useCase->execute($dto);
    }

    public function testShouldThrowExceptionForInvalidCPF(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid document number');

        $dto = new CreateUserDTO(
            username: 'johndoe',
            email: 'john@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '11111111111'
        );

        $this->useCase->execute($dto);
    }

    public function testShouldThrowExceptionForInvalidCNPJ(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid document number');

        $dto = new CreateUserDTO(
            username: 'companyxyz',
            email: 'contact@company.com',
            typeDoc: DocumentType::CNPJ,
            numberDoc: '11111111111111'
        );

        $this->useCase->execute($dto);
    }

    public function testShouldCreateMultipleUsersWithDifferentData(): void
    {
        $dto1 = new CreateUserDTO(
            username: 'user1',
            email: 'user1@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '11144477735'
        );

        $dto2 = new CreateUserDTO(
            username: 'user2',
            email: 'user2@example.com',
            typeDoc: DocumentType::CNPJ,
            numberDoc: '11222333000181'
        );

        $result1 = $this->useCase->execute($dto1);
        $result2 = $this->useCase->execute($dto2);

        $this->assertNotEquals($result1->id, $result2->id);
        $this->assertEquals('user1', $result1->username);
        $this->assertEquals('user2', $result2->username);
    }

    protected function tearDown(): void
    {
        // Limpa a coleção após cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }
}

