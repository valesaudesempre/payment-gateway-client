<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use ValeSaude\LaravelValueObjects\AbstractValueObject;
use ValeSaude\LaravelValueObjects\Casts\JsonSerializableValueObjectCast;
use ValeSaude\LaravelValueObjects\Contracts\JsonSerializableValueObjectInterface;
use ValeSaude\LaravelValueObjects\Month;
use ValeSaude\LaravelValueObjects\PositiveInteger;

class CreditCard extends AbstractValueObject implements Arrayable, Castable, JsonSerializableValueObjectInterface
{
    private string $holderName;
    private string $number;
    private string $brand;
    private Month $expirationMonth;
    private PositiveInteger $expirationYear;

    public function __construct(
        string $holderName,
        string $number,
        string $brand,
        Month $expirationMonth,
        PositiveInteger $expirationYear
    ) {
        $this->holderName = $holderName;
        $this->number = $number;
        $this->brand = $brand;
        $this->expirationMonth = $expirationMonth;
        $this->expirationYear = $expirationYear;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'holder_name' => $this->holderName,
            'number' => $this->number,
            'brand' => $this->brand,
            'expiration_month' => $this->expirationMonth->getValue(),
            'expiration_year' => $this->expirationYear->getValue(),
        ];
    }

    public function getHolderName(): string
    {
        return $this->holderName;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getExpirationMonth(): Month
    {
        return $this->expirationMonth;
    }

    /**
     * @return PositiveInteger
     */
    public function getExpirationYear(): PositiveInteger
    {
        return $this->expirationYear;
    }

    /**
     * @param array{
     *     holder_name: string,
     *     number: string,
     *     brand: string,
     *     expiration_month: int,
     *     expiration_year: int
     * } $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self(
            $attributes['holder_name'],
            $attributes['number'],
            $attributes['brand'],
            new Month($attributes['expiration_month']),
            new PositiveInteger($attributes['expiration_year'])
        );
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new JsonSerializableValueObjectCast(static::class);
    }
}
