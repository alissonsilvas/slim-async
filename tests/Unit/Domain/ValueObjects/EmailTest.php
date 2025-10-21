<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testShouldCreateEmailWithValidFormat(): void
    {
        $email = Email::fromString('test@example.com');
        
        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals('test@example.com', $email->toString());
    }

    public function testShouldThrowExceptionForInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        Email::fromString('invalid-email');
    }

    public function testShouldThrowExceptionForEmptyEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        Email::fromString('');
    }

    public function testShouldReturnTrueForEqualEmails(): void
    {
        $email1 = Email::fromString('test@example.com');
        $email2 = Email::fromString('test@example.com');
        
        $this->assertTrue($email1->equals($email2));
    }

    public function testShouldReturnFalseForDifferentEmails(): void
    {
        $email1 = Email::fromString('test@example.com');
        $email2 = Email::fromString('other@example.com');
        
        $this->assertFalse($email1->equals($email2));
    }
}
