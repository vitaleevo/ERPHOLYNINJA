<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AngolanDocument implements ValidationRule
{
    public function __construct(
        private string $type
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        match ($this->type) {
            'bi' => $this->validateBI($attribute, $value, $fail),
            'nif' => $this->validateNIF($attribute, $value, $fail),
            'passport' => $this->validatePassport($attribute, $value, $fail),
            default => $fail('Tipo de documento inválido.'),
        };
    }

    private function validateBI(string $attribute, string $value, Closure $fail): void
    {
        // BI angolano: 8 dígitos + letra (ex: 00245384MA10)
        $pattern = '/^\d{8}[A-Z]{2}\d{2}$/i';
        
        if (!preg_match($pattern, str_replace(['-', ' '], '', $value))) {
            $fail('O :attribute deve ser um BI válido (ex: 00245384MA10).');
        }
    }

    private function validateNIF(string $attribute, string $value, Closure $fail): void
    {
        // NIF angolano: 9 dígitos
        $pattern = '/^\d{9}$/';
        
        if (!preg_match($pattern, $value)) {
            $fail('O :attribute deve conter 9 dígitos.');
        }
        
        // Validação do dígito verificador
        if (!$this->validateNIFChecksum($value)) {
            $fail('O :attribute tem um número inválido.');
        }
    }

    private function validatePassport(string $attribute, string $value, Closure $fail): void
    {
        // Passaporte: formato variável, validar mínimo
        if (strlen($value) < 6 || strlen($value) > 12) {
            $fail('O :attribute deve ter entre 6 e 12 caracteres.');
        }
    }

    private function validateNIFChecksum(string $nif): bool
    {
        // Algoritmo de validação do NIF angolano
        $digits = str_split($nif);
        $sum = 0;
        
        for ($i = 0; $i < 8; $i++) {
            $sum += intval($digits[$i]) * (9 - $i);
        }
        
        $remainder = $sum % 11;
        $checkDigit = $remainder === 0 ? 0 : (11 - $remainder);
        
        return intval($digits[8]) === $checkDigit;
    }
}
