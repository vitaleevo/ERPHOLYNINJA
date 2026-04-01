<?php

namespace App\Modules\Core\Shared\ValueObjects;

use InvalidArgumentException;

class Money
{
    public function __construct(
        private readonly float $amount,
        private readonly string $currency = 'AOA'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('O valor não pode ser negativo');
        }
    }

    public static function fromFloat(float $amount, string $currency = 'AOA'): self
    {
        return new self($amount, $currency);
    }

    public static function fromInt(int $amount, string $currency = 'AOA'): self
    {
        return new self((float) $amount, $currency);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): Money
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Moedas diferentes não podem ser somadas');
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): Money
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Moedas diferentes não podem ser subtraídas');
        }

        $result = $this->amount - $other->amount;
        
        if ($result < 0) {
            throw new InvalidArgumentException('Resultado não pode ser negativo');
        }

        return new self($result, $this->currency);
    }

    public function multiply(float $factor): Money
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Fator deve ser positivo');
        }

        return new self($this->amount * $factor, $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount 
            && $this->currency === $other->currency;
    }

    public function isGreaterThan(Money $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Moedas diferentes não podem ser comparadas');
        }

        return $this->amount > $other->amount;
    }

    public function isLessThan(Money $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Moedas diferentes não podem ser comparadas');
        }

        return $this->amount < $other->amount;
    }

    public function isZero(): bool
    {
        return $this->amount === 0.0;
    }

    public function toString(): string
    {
        return number_format($this->amount, 2, ',', '.') . ' ' . $this->currency;
    }
}
