<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Enums\DocumentType;

final class Document
{
    private function __construct(
        private DocumentType $type,
        private string $number
    ) {
    }

    public static function create(DocumentType $type, string $number): self
    {
        $cleanNumber = preg_replace('/\D/', '', $number);

        if (strlen($cleanNumber) !== $type->getLength()) {
            throw new \InvalidArgumentException(
                sprintf('%s must have %d digits', $type->value, $type->getLength())
            );
        }

        if (!self::isValid($type, $cleanNumber)) {
            throw new \InvalidArgumentException('Invalid document number');
        }

        return new self($type, $cleanNumber);
    }

    private static function isValid(DocumentType $type, string $number): bool
    {
        return match ($type) {
            DocumentType::CPF => self::isValidCPF($number),
            DocumentType::CNPJ => self::isValidCNPJ($number),
        };
    }

    private static function isValidCPF(string $cpf): bool
    {
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcula o primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $firstDigit = ($remainder < 2) ? 0 : 11 - $remainder;

        if (intval($cpf[9]) !== $firstDigit) {
            return false;
        }

        // Calcula o segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $secondDigit = ($remainder < 2) ? 0 : 11 - $remainder;

        return intval($cpf[10]) === $secondDigit;
    }

    private static function isValidCNPJ(string $cnpj): bool
    {
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Calcula o primeiro dígito verificador
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += intval($cnpj[$i]) * $weights[$i];
        }
        $remainder = $sum % 11;
        $firstDigit = ($remainder < 2) ? 0 : 11 - $remainder;

        if (intval($cnpj[12]) !== $firstDigit) {
            return false;
        }

        // Calcula o segundo dígito verificador
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += intval($cnpj[$i]) * $weights[$i];
        }
        $remainder = $sum % 11;
        $secondDigit = ($remainder < 2) ? 0 : 11 - $remainder;

        return intval($cnpj[13]) === $secondDigit;
    }

    public function getType(): DocumentType
    {
        return $this->type;
    }

    public function getNumber(): string
    {
        return $this->number;
    }
}
