<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Domain\Entities\User;
use App\Domain\Enums\DocumentType;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Username;
use App\Domain\ValueObjects\Document;
use App\Infrastructure\Mongo\MongoConnection;
use App\Infrastructure\Repositories\MongoUserRepository;
use PHPUnit\Framework\TestCase;

class MongoUserRepositoryTest extends TestCase
{
    private MongoUserRepository $repository;
    private MongoConnection $connection;

    protected function setUp(): void
    {
        $this->connection = new MongoConnection();
        $this->repository = new MongoUserRepository($this->connection);
        
        // Limpa a coleção antes de cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }

    public function testShouldSaveAndFindUserById(): void
    {
        $user = $this->createUser();
        
        $this->repository->save($user);
        
        $foundUser = $this->repository->findById($user->getId());
        
        $this->assertNotNull($foundUser);
        $this->assertEquals($user->getId(), $foundUser->getId());
        $this->assertEquals($user->getUsername()->toString(), $foundUser->getUsername()->toString());
        $this->assertEquals($user->getEmail()->toString(), $foundUser->getEmail()->toString());
    }

    public function testShouldFindUserByEmail(): void
    {
        $user = $this->createUser();
        $this->repository->save($user);
        
        $foundUser = $this->repository->findByEmail($user->getEmail()->toString());
        
        $this->assertNotNull($foundUser);
        $this->assertEquals($user->getId(), $foundUser->getId());
    }

    public function testShouldFindUserByUsername(): void
    {
        $user = $this->createUser();
        $this->repository->save($user);
        
        $foundUser = $this->repository->findByUsername($user->getUsername()->toString());
        
        $this->assertNotNull($foundUser);
        $this->assertEquals($user->getId(), $foundUser->getId());
    }

    public function testShouldReturnNullForNonExistentUser(): void
    {
        $user = $this->repository->findById('non-existent-id');
        
        $this->assertNull($user);
    }

    public function testShouldDeleteUser(): void
    {
        $user = $this->createUser();
        $this->repository->save($user);
        
        $result = $this->repository->delete($user->getId());
        
        $this->assertTrue($result);
        
        $deletedUser = $this->repository->findById($user->getId());
        $this->assertNull($deletedUser);
    }

    public function testShouldReturnFalseWhenDeletingNonExistentUser(): void
    {
        $result = $this->repository->delete('non-existent-id');
        
        $this->assertFalse($result);
    }

    public function testShouldCheckEmailExists(): void
    {
        $user = $this->createUser();
        $this->repository->save($user);
        
        $this->assertTrue($this->repository->existsByEmail($user->getEmail()->toString()));
        $this->assertFalse($this->repository->existsByEmail('other@example.com'));
    }

    public function testShouldCheckUsernameExists(): void
    {
        $user = $this->createUser();
        $this->repository->save($user);
        
        $this->assertTrue($this->repository->existsByUsername($user->getUsername()->toString()));
        $this->assertFalse($this->repository->existsByUsername('otheruser'));
    }

    public function testShouldFindAllUsersWithPagination(): void
    {
        // Criar 5 usuários
        for ($i = 1; $i <= 5; $i++) {
            $user = $this->createUser("user{$i}", "user{$i}@example.com");
            $this->repository->save($user);
        }
        
        $users = $this->repository->findAll(1, 3);
        
        $this->assertCount(3, $users);
        $this->assertContainsOnlyInstancesOf(User::class, $users);
    }

    public function testShouldUpdateExistingUser(): void
    {
        $user = $this->createUser();
        $this->repository->save($user);
        
        $newUsername = Username::fromString('updateduser');
        $newEmail = Email::fromString('updated@example.com');
        
        $user->update($newUsername, $newEmail);
        $this->repository->save($user);
        
        $updatedUser = $this->repository->findById($user->getId());
        
        $this->assertNotNull($updatedUser);
        $this->assertEquals('updateduser', $updatedUser->getUsername()->toString());
        $this->assertEquals('updated@example.com', $updatedUser->getEmail()->toString());
    }

    protected function tearDown(): void
    {
        // Limpa a coleção após cada teste
        $collection = $this->connection->collection('users');
        $collection->deleteMany([]);
    }

    private function createUser(string $username = 'johndoe', string $email = 'john@example.com'): User
    {
        return User::create(
            Username::fromString($username),
            Email::fromString($email),
            Document::create(DocumentType::CPF, '11144477735')
        );
    }
}
