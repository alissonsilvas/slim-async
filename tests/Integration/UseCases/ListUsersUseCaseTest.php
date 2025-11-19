<?php

declare(strict_types=1);

namespace Tests\Integration\UseCases;

use App\Application\UseCases\User\ListUsersUseCase;
use App\Domain\Entities\User;
use App\Domain\Enums\DocumentType;
use App\Domain\ValueObjects\Document;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Username;
use App\Infrastructure\Mongo\MongoConnection;
use App\Infrastructure\Repositories\MongoUserRepository;
use PHPUnit\Framework\TestCase;

class ListUsersUseCaseTest extends TestCase
{
    private ListUsersUseCase $useCase;
    private MongoUserRepository $repository;
    private MongoConnection $connection;

    protected function setUp(): void
    {
        $this->connection = new MongoConnection();
        $this->repository = new MongoUserRepository($this->connection);
        $this->useCase = new ListUsersUseCase($this->repository);

        // Limpa a coleção antes de cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }

    public function testShouldListAllUsers(): void
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

        $result = $this->useCase->execute();

        $this->assertCount(2, $result);
        $this->assertEquals('user1', $result[0]->username);
        $this->assertEquals('user2', $result[1]->username);
    }

    public function testShouldReturnEmptyArrayWhenNoUsers(): void
    {
        $result = $this->useCase->execute();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testShouldPaginateResults(): void
    {
        // Cria 15 usuários
        for ($i = 1; $i <= 15; $i++) {
            $user = User::create(
                Username::fromString("user{$i}"),
                Email::fromString("user{$i}@example.com"),
                Document::create(DocumentType::CPF, '11144477735')
            );
            $this->repository->save($user);
        }

        // Primeira página (10 usuários)
        $page1 = $this->useCase->execute(1, 10);
        $this->assertCount(10, $page1);

        // Segunda página (5 usuários)
        $page2 = $this->useCase->execute(2, 10);
        $this->assertCount(5, $page2);
    }

    public function testShouldRespectLimitParameter(): void
    {
        // Cria 10 usuários
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create(
                Username::fromString("user{$i}"),
                Email::fromString("user{$i}@example.com"),
                Document::create(DocumentType::CPF, '11144477735')
            );
            $this->repository->save($user);
        }

        $result = $this->useCase->execute(1, 5);

        $this->assertCount(5, $result);
    }

    public function testShouldReturnEmptyArrayForPageBeyondResults(): void
    {
        $user = User::create(
            Username::fromString('user1'),
            Email::fromString('user1@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);

        $result = $this->useCase->execute(10, 10);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testShouldListUsersWithDifferentDocumentTypes(): void
    {
        $userCPF = User::create(
            Username::fromString('usercpf'),
            Email::fromString('cpf@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $userCNPJ = User::create(
            Username::fromString('usercnpj'),
            Email::fromString('cnpj@example.com'),
            Document::create(DocumentType::CNPJ, '11222333000181')
        );

        $this->repository->save($userCPF);
        $this->repository->save($userCNPJ);

        $result = $this->useCase->execute();

        $this->assertCount(2, $result);
        $this->assertEquals('CPF', $result[0]->typeDoc);
        $this->assertEquals('CNPJ', $result[1]->typeDoc);
    }

    public function testShouldReturnUsersInCorrectOrder(): void
    {
        // Cria usuários com delay para garantir ordem diferente
        $user1 = User::create(
            Username::fromString('first'),
            Email::fromString('first@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );
        $this->repository->save($user1);

        sleep(1); // 1 segundo de delay

        $user2 = User::create(
            Username::fromString('second'),
            Email::fromString('second@example.com'),
            Document::create(DocumentType::CPF, '52998224725')
        );
        $this->repository->save($user2);

        $result = $this->useCase->execute();

        $this->assertCount(2, $result);
        // Os mais recentes devem vir primeiro (ordem decrescente por created_at)
        $this->assertEquals('second', $result[0]->username);
        $this->assertEquals('first', $result[1]->username);
    }

    public function testShouldIncludeAllUserFields(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);

        $result = $this->useCase->execute();

        $this->assertCount(1, $result);
        $userData = $result[0];

        $this->assertNotEmpty($userData->id);
        $this->assertNotEmpty($userData->username);
        $this->assertNotEmpty($userData->email);
        $this->assertNotEmpty($userData->typeDoc);
        $this->assertNotEmpty($userData->numberDoc);
        $this->assertNotEmpty($userData->createdAt);
        $this->assertNotEmpty($userData->updatedAt);
    }

    public function testShouldHandleCustomPageSize(): void
    {
        // Cria 20 usuários
        for ($i = 1; $i <= 20; $i++) {
            $user = User::create(
                Username::fromString("user{$i}"),
                Email::fromString("user{$i}@example.com"),
                Document::create(DocumentType::CPF, '11144477735')
            );
            $this->repository->save($user);
        }

        // Testa diferentes tamanhos de página
        $result5 = $this->useCase->execute(1, 5);
        $result10 = $this->useCase->execute(1, 10);
        $result15 = $this->useCase->execute(1, 15);

        $this->assertCount(5, $result5);
        $this->assertCount(10, $result10);
        $this->assertCount(15, $result15);
    }

    protected function tearDown(): void
    {
        // Limpa a coleção após cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }
}

