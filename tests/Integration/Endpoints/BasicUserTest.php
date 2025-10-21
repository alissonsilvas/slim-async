<?php

declare(strict_types=1);

namespace Tests\Integration\Endpoints;

use App\Domain\Entities\User;
use App\Domain\Enums\DocumentType;
use App\Domain\ValueObjects\Document;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Username;
use App\Infrastructure\Mongo\MongoConnection;
use App\Infrastructure\Repositories\MongoUserRepository;
use PHPUnit\Framework\TestCase;

class BasicUserTest extends TestCase
{
    private MongoConnection $connection;
    private MongoUserRepository $repository;

    protected function setUp(): void
    {
        $this->connection = new MongoConnection();
        $this->repository = new MongoUserRepository($this->connection);
        
        // Limpa a coleção antes de cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }

    public function testShouldCreateAndFindUser(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        // Salva o usuário
        $this->repository->save($user);
        
        // Busca por email
        $foundUser = $this->repository->findByEmail('john@example.com');
        
        $this->assertNotNull($foundUser);
        $this->assertEquals('johndoe', $foundUser->getUsername()->toString());
        $this->assertEquals('john@example.com', $foundUser->getEmail()->toString());
    }

    public function testShouldFindUserByUsername(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);
        
        $foundUser = $this->repository->findByUsername('johndoe');
        
        $this->assertNotNull($foundUser);
        $this->assertEquals('johndoe', $foundUser->getUsername()->toString());
    }

    public function testShouldListUsers(): void
    {
        // Cria usuário 1
        $user1 = User::create(
            Username::fromString('user1'),
            Email::fromString('user1@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );
        
        // Cria usuário 2
        $user2 = User::create(
            Username::fromString('user2'),
            Email::fromString('user2@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user1);
        $this->repository->save($user2);
        
        $users = $this->repository->findAll();
        
        $this->assertCount(2, $users);
    }

    public function testShouldDeleteUser(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);
        
        // Busca o usuário para obter o ID
        $foundUser = $this->repository->findByEmail('john@example.com');
        $this->assertNotNull($foundUser);
        
        // Deleta o usuário
        $result = $this->repository->delete($foundUser->getId());
        
        $this->assertTrue($result);
        
        // Verifica se foi deletado
        $deletedUser = $this->repository->findByEmail('john@example.com');
        $this->assertNull($deletedUser);
    }

    public function testShouldCheckEmailExists(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);
        
        $this->assertTrue($this->repository->existsByEmail('john@example.com'));
        $this->assertFalse($this->repository->existsByEmail('nonexistent@example.com'));
    }

    public function testShouldCheckUsernameExists(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $this->repository->save($user);
        
        $this->assertTrue($this->repository->existsByUsername('johndoe'));
        $this->assertFalse($this->repository->existsByUsername('nonexistent'));
    }

    public function testShouldHandlePagination(): void
    {
        // Cria 5 usuários
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create(
                Username::fromString("user{$i}"),
                Email::fromString("user{$i}@example.com"),
                Document::create(DocumentType::CPF, '11144477735')
            );
            $this->repository->save($user);
        }
        
        // Primeira página com 2 itens
        $usersPage1 = $this->repository->findAll(1, 2);
        $this->assertCount(2, $usersPage1);
        
        // Segunda página com 2 itens
        $usersPage2 = $this->repository->findAll(2, 2);
        $this->assertCount(2, $usersPage2);
        
        // Terceira página com 2 itens
        $usersPage3 = $this->repository->findAll(3, 2);
        $this->assertCount(1, $usersPage3);
    }

    public function testShouldCreateUserWithCNPJ(): void
    {
        $user = User::create(
            Username::fromString('company'),
            Email::fromString('company@example.com'),
            Document::create(DocumentType::CNPJ, '11222333000181')
        );

        $this->repository->save($user);
        
        $foundUser = $this->repository->findByEmail('company@example.com');
        
        $this->assertNotNull($foundUser);
        $this->assertEquals('company', $foundUser->getUsername()->toString());
        $this->assertEquals('company@example.com', $foundUser->getEmail()->toString());
    }

    public function testShouldConvertUserToArray(): void
    {
        $user = User::create(
            Username::fromString('johndoe'),
            Email::fromString('john@example.com'),
            Document::create(DocumentType::CPF, '11144477735')
        );

        $userArray = $user->toArray();
        
        $this->assertIsArray($userArray);
        $this->assertArrayHasKey('id', $userArray);
        $this->assertArrayHasKey('username', $userArray);
        $this->assertArrayHasKey('email', $userArray);
        $this->assertArrayHasKey('type_doc', $userArray);
        $this->assertArrayHasKey('number_doc', $userArray);
        $this->assertArrayHasKey('created_at', $userArray);
        $this->assertArrayHasKey('updated_at', $userArray);
        
        $this->assertEquals('johndoe', $userArray['username']);
        $this->assertEquals('john@example.com', $userArray['email']);
        $this->assertEquals('CPF', $userArray['type_doc']);
        $this->assertEquals('11144477735', $userArray['number_doc']);
    }

    protected function tearDown(): void
    {
        // Limpa a coleção após cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }
}
