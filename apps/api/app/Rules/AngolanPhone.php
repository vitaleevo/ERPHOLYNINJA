<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AngolanPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Formato angolano: +244 XXX XXX XXX ou 9XX XXX XXX
        $pattern = '/^(\+244\s?)?9[1-7]\d{2}\s?\d{3}\s?\d{3}$/';
        
        if (!preg_match($pattern, preg_replace('/\s/', '', $value))) {
            $fail('O :attribute deve ser um número de telefone angolano válido (ex: +244 923 456 789).');
        }
    }
}
