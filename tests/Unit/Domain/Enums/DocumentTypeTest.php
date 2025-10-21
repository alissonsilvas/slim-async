<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Enums;

use App\Domain\Enums\DocumentType;
use PHPUnit\Framework\TestCase;

class DocumentTypeTest extends TestCase
{
    public function testShouldReturnCorrectLengthForCPF(): void
    {
        $this->assertEquals(11, DocumentType::CPF->getLength());
    }

    public function testShouldReturnCorrectLengthForCNPJ(): void
    {
        $this->assertEquals(14, DocumentType::CNPJ->getLength());
    }

    public function testShouldHaveCorrectStringValues(): void
    {
        $this->assertEquals('CPF', DocumentType::CPF->value);
        $this->assertEquals('CNPJ', DocumentType::CNPJ->value);
    }
}
