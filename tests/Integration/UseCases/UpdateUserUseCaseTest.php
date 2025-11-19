<?php

declare(strict_types=1);

namespace Tests\Integration\UseCases;

use App\Application\DTOs\UpdateUserDTO;
use App\Application\UseCases\User\UpdateUserUseCase;
use App\Domain\Entities\User;
use App\Domain\Enums\DocumentType;
use App\Domain\ValueObjects\Document;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Username;
use App\Infrastructure\Mongo\MongoConnection;
use App\Infrastructure\Repositories\MongoUserRepository;
use PHPUnit\Framework\TestCase;

class UpdateUserUseCaseTest extends TestCase
{
    private UpdateUserUseCase $useCase;
    private MongoUserRepository $repository;
    private MongoConnection $connection;

    protected function setUp(): void
    {
        $this->connection = new MongoConnection();
        $this->repository = new MongoUserRepository($this->connection);
        $this->useCase = new UpdateUserUseCase($this->repository);

        // Limpa a coleção antes de cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }

    public function testShouldUpdateUserUsername(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);

        $dto = new UpdateUserDTO(
            username: 'johnupdated',
            email: 'john@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '11144477735'
        );

        $result = $this->useCase->execute($user->getId(), $dto);

        $this->assertEquals('johnupdated', $result->username);
        $this->assertEquals('john@example.com', $result->email);
    }

    public function testShouldUpdateUserEmail(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);

        $dto = new UpdateUserDTO(
            username: 'johndoe',
            email: 'newemail@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '11144477735'
        );

        $result = $this->useCase->execute($user->getId(), $dto);

        $this->assertEquals('johndoe', $result->username);
        $this->assertEquals('newemail@example.com', $result->email);
    }

    public function testShouldUpdateUserDocument(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);

        $dto = new UpdateUserDTO(
            username: 'johndoe',
            email: 'john@example.com',
            typeDoc: DocumentType::CNPJ,
            numberDoc: '11222333000181'
        );

        $result = $this->useCase->execute($user->getId(), $dto);

        $this->assertEquals('CNPJ', $result->typeDoc);
        $this->assertEquals('11222333000181', $result->numberDoc);
    }

    public function testShouldUpdateMultipleFields(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);

        $dto = new UpdateUserDTO(
            username: 'janedoe',
            email: 'jane@example.com',
            typeDoc: DocumentType::CNPJ,
            numberDoc: '11222333000181'
        );

        $result = $this->useCase->execute($user->getId(), $dto);

        $this->assertEquals('janedoe', $result->username);
        $this->assertEquals('jane@example.com', $result->email);
        $this->assertEquals('CNPJ', $result->typeDoc);
        $this->assertEquals('11222333000181', $result->numberDoc);
    }

    public function testShouldThrowExceptionForNonExistentUser(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('User not found');

        $dto = new UpdateUserDTO(
            username: 'johndoe',
            email: 'john@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '11144477735'
        );

        $this->useCase->execute('non-existent-id', $dto);
    }

    public function testShouldThrowExceptionForDuplicateEmail(): void
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

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Email already exists');

        $dto = new UpdateUserDTO(
            username: 'user2',
            email: 'user1@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '22233344455'
        );

        $this->useCase->execute($user2->getId(), $dto);
    }

    public function testShouldThrowExceptionForDuplicateUsername(): void
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

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Username already exists');

        $dto = new UpdateUserDTO(
            username: 'user1',
            email: 'user2@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '22233344455'
        );

        $this->useCase->execute($user2->getId(), $dto);
    }

    public function testShouldAllowSameEmailForSameUser(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);

        $dto = new UpdateUserDTO(
            username: 'johnupdated',
            email: 'john@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '11144477735'
        );

        $result = $this->useCase->execute($user->getId(), $dto);

        $this->assertEquals('johnupdated', $result->username);
        $this->assertEquals('john@example.com', $result->email);
    }

    public function testShouldThrowExceptionForInvalidEmail(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        $dto = new UpdateUserDTO(
            username: 'johndoe',
            email: 'invalid-email',
            typeDoc: DocumentType::CPF,
            numberDoc: '11144477735'
        );

        $this->useCase->execute($user->getId(), $dto);
    }

    public function testShouldThrowExceptionForInvalidDocument(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid document number');

        $dto = new UpdateUserDTO(
            username: 'johndoe',
            email: 'john@example.com',
            typeDoc: DocumentType::CPF,
            numberDoc: '11111111111'
        );

        $this->useCase->execute($user->getId(), $dto);
    }

    protected function tearDown(): void
    {
        // Limpa a coleção após cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }
}

