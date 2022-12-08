<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use ValeSaude\PaymentGatewayClient\Casts\JsonSerializableValueObjectCast;
use ValeSaude\PaymentGatewayClient\Utils\JSON;
use ValeSaude\PaymentGatewayClient\ValueObjects\Contracts\JsonSerializableValueObjectInterface;

class JsonObject extends AbstractValueObject implements Arrayable, JsonSerializableValueObjectInterface, Castable
{
    /**
     * @var array<array-key, mixed>
     */
    private array $content;

    /**
     * @param array<string, mixed> $content
     */
    public function __construct(array $content)
    {
        $this->content = $content;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->content;
    }

    /**
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return data_get($this->content, $key, $default);
    }

    /**
     * @param mixed $value
     */
    public function set(string $key, $value): self
    {
        $content = $this->content;

        data_set($content, $key, $value);

        return new self($content);
    }

    public function isEmpty(): bool
    {
        return empty($this->content);
    }

    public static function fromString(string $json): self
    {
        return new self(JSON::decode($json));
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function fromArray(array $attributes): self
    {
        return new self($attributes);
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new JsonSerializableValueObjectCast(static::class);
    }

    public static function empty(): self
    {
        return new self([]);
    }
}
