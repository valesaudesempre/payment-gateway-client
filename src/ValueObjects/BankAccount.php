<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use ValeSaude\PaymentGatewayClient\Casts\JsonSerializableValueObjectCast;
use ValeSaude\PaymentGatewayClient\ValueObjects\Contracts\JsonSerializableValueObjectInterface;

class BankAccount extends AbstractValueObject implements Arrayable, Castable, JsonSerializableValueObjectInterface
{
    private Bank $bank;
    private string $agencyNumber;
    private ?string $agencyCheckDigit;
    private string $accountNumber;
    private ?string $accountCheckDigit;

    public function __construct(
        Bank $bank,
        string $agencyNumber,
        ?string $agencyCheckDigit,
        string $accountNumber,
        ?string $accountCheckDigit
    ) {
        $this->bank = $bank;
        $this->agencyNumber = $agencyNumber;
        $this->agencyCheckDigit = $agencyCheckDigit;
        $this->accountNumber = $accountNumber;
        $this->accountCheckDigit = $accountCheckDigit;
    }

    /**
     * @return array{
     *     bank: string,
     *     agency_number: string,
     *     agency_check_digit: string|null,
     *     account_number: string,
     *     account_check_digit: string|null
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array{
     *     bank: string,
     *     agency_number: string,
     *     agency_check_digit: string|null,
     *     account_number: string,
     *     account_check_digit: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'bank' => (string) $this->bank,
            'agency_number' => $this->agencyNumber,
            'agency_check_digit' => $this->agencyCheckDigit,
            'account_number' => $this->accountNumber,
            'account_check_digit' => $this->accountCheckDigit,
        ];
    }

    public function getBank(): Bank
    {
        return $this->bank;
    }

    public function getAgencyNumber(): string
    {
        return $this->agencyNumber;
    }

    public function getAgencyCheckDigit(): ?string
    {
        return $this->agencyCheckDigit;
    }

    public function getAgencyFormatted(): string
    {
        if (!$this->agencyCheckDigit) {
            return $this->agencyNumber;
        }

        return "{$this->agencyNumber}-{$this->agencyCheckDigit}";
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function getAccountCheckDigit(): ?string
    {
        return $this->accountCheckDigit;
    }

    public function getAccountFormatted(): string
    {
        if (!$this->accountCheckDigit) {
            return $this->accountNumber;
        }

        return "{$this->accountNumber}-{$this->accountCheckDigit}";
    }

    /**
     * @param array{
     *     bank: string,
     *     agency_number: string,
     *     agency_check_digit: string|null,
     *     account_number: string,
     *     account_check_digit: string|null
     * } $attributes
     */
    public static function fromArray(array $attributes): self
    {
        // @phpstan-ignore
        return new self(
            new Bank($attributes['bank']),
            $attributes['agency_number'],
            $attributes['agency_check_digit'],
            $attributes['account_number'],
            $attributes['account_check_digit'],
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
