<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use ValeSaude\PaymentGatewayClient\Casts\MoneyCast;

class Money extends AbstractValueObject implements Castable
{
    private int $cents;

    public function __construct(int $cents)
    {
        $this->cents = $cents;
    }

    public function getCents(): int
    {
        return $this->cents;
    }

    public function toFloat(): float
    {
        return round($this->cents, 2);
    }

    /**
     * @param int|float $multiplier
     *
     * @return self
     */
    public function multiply($multiplier): self
    {
        return new self((int) round($this->cents * $multiplier));
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public static function castUsing(array $arguments): MoneyCast
    {
        return new MoneyCast();
    }

    public static function zero(): self
    {
        return new self(0);
    }
}
