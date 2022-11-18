<?php

namespace ValeSaude\PaymentGatewayClient\Utils;

use JsonException;
use JsonSerializable;

final class JSON
{
    /**
     * @throws JsonException
     *
     * @return array<array-key, mixed>
     */
    public static function decode(string $json): array
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param JsonSerializable|mixed $data
     *
     * @throws JsonException
     *
     * @return string
     */
    public static function encode($data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
