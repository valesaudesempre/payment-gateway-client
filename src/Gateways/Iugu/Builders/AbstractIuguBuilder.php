<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu\Builders;

abstract class AbstractIuguBuilder
{
    /**
     * @var array<string, mixed>
     */
    protected array $properties;

    protected function __construct()
    {
        $this->properties = [];
    }

    public function setExternalReference(string $externalReference): self
    {
        $this->properties['custom_variables'] = [
            [
                'name' => 'external_reference',
                'value' => $externalReference,
            ],
        ];

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return $this->properties;
    }

    /**
     * @return static
     */
    public static function make(): self
    {
        return new static();
    }
}
