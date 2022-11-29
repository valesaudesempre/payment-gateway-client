<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu\Exceptions;

use Throwable;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\GatewayException;

/**
 * @template TKey
 * @template TValue
 */
class GenericErrorResponseException extends GatewayException
{
    /**
     * @var array<TKey|array-key, TValue|string>
     */
    private array $errors;

    /**
     * @param string                               $message
     * @param array<TKey|array-key, TValue|string> $errors
     */
    public function __construct(string $message, array $errors, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * @return array<TKey|array-key, TValue|string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array<TKey|array-key, TValue|string> $errors
     */
    public static function withErrors(array $errors): self
    {
        return new self('There was an error during the request.', $errors);
    }
}
