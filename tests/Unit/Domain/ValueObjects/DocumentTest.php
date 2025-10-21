<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\Enums\DocumentType;
use App\Domain\ValueObjects\Document;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    public function testShouldCreateValidCPF(): void
    {
        $document = Document::create(DocumentType::CPF, '11144477735');
        
        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals(DocumentType::CPF, $document->getType());
        $this->assertEquals('11144477735', $document->getNumber());
    }

    public function testShouldCreateValidCNPJ(): void
    {
        $document = Document::create(DocumentType::CNPJ, '11222333000181');
        
        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals(DocumentType::CNPJ, $document->getType());
        $this->assertEquals('11222333000181', $document->getNumber());
    }

    public function testShouldCleanNonNumericCharacters(): void
    {
        $document = Document::create(DocumentType::CPF, '111.444.777-35');
        
        $this->assertEquals('11144477735', $document->getNumber());
    }

    public function testShouldThrowExceptionForInvalidCPFLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CPF must have 11 digits');
        
        Document::create(DocumentType::CPF, '123456789');
    }

    public function testShouldThrowExceptionForInvalidCNPJLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CNPJ must have 14 digits');
        
        Document::create(DocumentType::CNPJ, '1234567890123');
    }

    public function testShouldThrowExceptionForInvalidCPF(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid document number');
        
        Document::create(DocumentType::CPF, '11111111111');
    }

    public function testShouldThrowExceptionForInvalidCNPJ(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid document number');
        
        Document::create(DocumentType::CNPJ, '11111111111111');
    }
}
