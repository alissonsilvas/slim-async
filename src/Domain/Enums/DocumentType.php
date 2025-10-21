<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum DocumentType: string
{
    case CPF = 'CPF';
    case CNPJ = 'CNPJ';

    public function getLength(): int
    {
        return match ($this) {
            self::CPF => 11,
            self::CNPJ => 14,
        };
    }
}
