<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\Username;
use PHPUnit\Framework\TestCase;

class UsernameTest extends TestCase
{
    public function testShouldCreateUsernameWithValidFormat(): void
    {
        $username = Username::fromString('johndoe');
        
        $this->assertInstanceOf(Username::class, $username);
        $this->assertEquals('johndoe', $username->toString());
    }

    public function testShouldCreateUsernameWithNumbersAndUnderscores(): void
    {
        $username = Username::fromString('john_doe123');
        
        $this->assertInstanceOf(Username::class, $username);
        $this->assertEquals('john_doe123', $username->toString());
    }

    public function testShouldThrowExceptionForTooShortUsername(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Username must be between 3 and 50 characters');
        
        Username::fromString('jo');
    }

    public function testShouldThrowExceptionForTooLongUsername(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Username must be between 3 and 50 characters');
        
        Username::fromString(str_repeat('a', 51));
    }

    public function testShouldThrowExceptionForInvalidCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Username can only contain letters, numbers and underscores');
        
        Username::fromString('john-doe');
    }

    public function testShouldReturnTrueForEqualUsernames(): void
    {
        $username1 = Username::fromString('johndoe');
        $username2 = Username::fromString('johndoe');
        
        $this->assertTrue($username1->equals($username2));
    }

    public function testShouldReturnFalseForDifferentUsernames(): void
    {
        $username1 = Username::fromString('johndoe');
        $username2 = Username::fromString('janedoe');
        
        $this->assertFalse($username1->equals($username2));
    }
}
