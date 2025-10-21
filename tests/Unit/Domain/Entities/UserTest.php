<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entities;

use App\Domain\Entities\User;
use App\Domain\Enums\DocumentType;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Username;
use App\Domain\ValueObjects\Document;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testShouldCreateUserWithValidData(): void
    {
        $username = Username::fromString('johndoe');
        $email = Email::fromString('john@example.com');
        $document = Document::create(DocumentType::CPF, '11144477735');
        
        $user = User::create($username, $email, $document);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->getId());
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($document, $user->getDocument());
        $this->assertInstanceOf(\DateTime::class, $user->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $user->getUpdatedAt());
    }

    public function testShouldUpdateUsername(): void
    {
        $user = $this->createUser();
        $newUsername = Username::fromString('janedoe');
        
        $user->update(username: $newUsername);
        
        $this->assertEquals($newUsername, $user->getUsername());
    }

    public function testShouldUpdateEmail(): void
    {
        $user = $this->createUser();
        $newEmail = Email::fromString('jane@example.com');
        
        $user->update(email: $newEmail);
        
        $this->assertEquals($newEmail, $user->getEmail());
    }

    public function testShouldUpdateDocument(): void
    {
        $user = $this->createUser();
        $newDocument = Document::create(DocumentType::CNPJ, '11222333000181');
        
        $user->update(document: $newDocument);
        
        $this->assertEquals($newDocument, $user->getDocument());
    }

    public function testShouldUpdateMultipleFields(): void
    {
        $user = $this->createUser();
        $newUsername = Username::fromString('janedoe');
        $newEmail = Email::fromString('jane@example.com');
        
        $user->update(username: $newUsername, email: $newEmail);
        
        $this->assertEquals($newUsername, $user->getUsername());
        $this->assertEquals($newEmail, $user->getEmail());
    }

    public function testShouldUpdateUpdatedAtWhenModified(): void
    {
        $user = $this->createUser();
        $originalUpdatedAt = $user->getUpdatedAt();
        
        // Aguarda um pouco para garantir diferença de tempo
        usleep(1000);
        
        $newUsername = Username::fromString('janedoe');
        $user->update(username: $newUsername);
        
        $this->assertGreaterThan($originalUpdatedAt, $user->getUpdatedAt());
    }

    public function testShouldConvertToArray(): void
    {
        $user = $this->createUser();
        $array = $user->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('username', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('type_doc', $array);
        $this->assertArrayHasKey('number_doc', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
    }

    private function createUser(): User
    {
        $username = Username::fromString('johndoe');
        $email = Email::fromString('john@example.com');
        $document = Document::create(DocumentType::CPF, '11144477735');
        
        return User::create($username, $email, $document);
    }
}
