<?php

namespace ValeSaude\PaymentGatewayClient\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use JsonException;
use ValeSaude\PaymentGatewayClient\Collections\AbstractCollection;
use ValeSaude\PaymentGatewayClient\Utils\JSON;

/**
 * @template TSubject
 * @template TCollection of AbstractCollection
 */
abstract class AbstractCollectionCast implements CastsAttributes
{
    /**
     * @return class-string<TCollection>
     */
    abstract public function getCollectionClass(): string;

    /**
     * @param mixed $item
     *
     * @return TSubject
     */
    abstract public function castToSubject($item);

    /**
     * @param string|null          $value
     * @param array<string, mixed> $attributes
     *
     * @throws JsonException
     *
     * @return TCollection
     */
    public function get($model, string $key, $value, array $attributes): ?AbstractCollection
    {
        if (null === $value) {
            return null;
        }

        $items = JSON::decode($value);
        $class = $this->getCollectionClass();

        return new $class(array_map([$this, 'castToSubject'], $items));
    }

    /**
     * @param TCollection|null     $value
     * @param array<string, mixed> $attributes
     *
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if (null === $value) {
            return null;
        }

        return JSON::encode($value);
    }
}
