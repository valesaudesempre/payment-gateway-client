<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use ValeSaude\PaymentGatewayClient\ValueObjects\Contracts\JsonSerializableValueObjectInterface;

class InvoiceSplitRule extends AbstractValueObject implements Arrayable, JsonSerializableValueObjectInterface
{
    private string $recipientId;
    private Money $amount;

    public function __construct(string $recipientId, Money $amount)
    {
        $this->recipientId = $recipientId;
        $this->amount = $amount;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array{recipient_id: string, amount: int}
     */
    public function toArray(): array
    {
        return [
            'recipient_id' => $this->recipientId,
            'amount' => $this->amount->getCents(),
        ];
    }

    /**
     * @return string
     */
    public function getRecipientId(): string
    {
        return $this->recipientId;
    }

    /**
     * @return Money
     */
    public function getAmount(): Money
    {
        return $this->amount;
    }

    /**
     * @param array{recipient_id: string, amount: int} $attributes
     *
     * @return self
     */
    public static function fromArray(array $attributes): self
    {
        return new self(
            $attributes['recipient_id'],
            new Money($attributes['amount'])
        );
    }
}
