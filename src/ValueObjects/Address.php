<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use JsonException;
use JsonSerializable;
use ValeSaude\PaymentGatewayClient\Utils\JSON;

class Address extends AbstractValueObject implements Arrayable, JsonSerializable, Castable
{
    private ZipCode $zipCode;
    private string $street;
    private string $number;
    private string $district;
    private string $city;
    private string $state;
    private ?string $complement;

    public function __construct(
        ZipCode $zipCode,
        string $street,
        string $number,
        string $district,
        string $city,
        string $state,
        ?string $complement = null
    ) {
        $this->zipCode = $zipCode;
        $this->street = $street;
        $this->number = $number;
        $this->district = $district;
        $this->city = $city;
        $this->state = $state;
        $this->complement = $complement;
    }

    /**
     * @return array<string, string|null>
     */
    public function jsonSerialize(): array
    {
        return [
            'zip_code' => (string) $this->zipCode,
            'street' => $this->street,
            'number' => $this->number,
            'district' => $this->district,
            'city' => $this->city,
            'state' => $this->state,
            'complement' => $this->complement,
        ];
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function getZipCode(): ZipCode
    {
        return $this->zipCode;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getDistrict(): string
    {
        return $this->district;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    /**
     * @param array{
     *     zip_code: string,
     *     street: string,
     *     number: string,
     *     district: string,
     *     city: string,
     *     state: string,
     *     complement: string|null
     * } $attributes
     */
    public static function fromArray(array $attributes): self
    {
        // @phpstan-ignore
        return new self(
            new ZipCode($attributes['zip_code']),
            $attributes['street'],
            $attributes['number'],
            $attributes['district'],
            $attributes['city'],
            $attributes['state'],
            $attributes['complement'],
        );
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class() implements CastsAttributes {
            /**
             * @param string               $value
             * @param array<string, mixed> $attributes
             *
             * @throws JsonException
             */
            public function get($model, string $key, $value, array $attributes): Address
            {
                // @phpstan-ignore-next-line
                return Address::fromArray(JSON::decode($value));
            }

            /**
             * @param Address              $value
             * @param array<string, mixed> $attributes
             *
             * @throws JsonException
             */
            public function set($model, string $key, $value, array $attributes): string
            {
                return JSON::encode($value);
            }
        };
    }
}
