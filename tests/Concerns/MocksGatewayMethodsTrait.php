<?php

namespace ValeSaude\PaymentGatewayClient\Tests\Concerns;

use PHPUnit\Framework\MockObject\MockObject;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Tests\TestCase;

/**
 * @mixin TestCase
 */
trait MocksGatewayMethodsTrait
{
    /**
     * @var GatewayInterface&MockObject|null
     */
    protected ?GatewayInterface $gatewayMock = null;

    /**
     * @return GatewayInterface&MockObject
     */
    public function createGatewayMock(): GatewayInterface
    {
        $this->gatewayMock = $this->createMock(GatewayInterface::class);

        return $this->gatewayMock;
    }

    public function mockGatewaySupportedFeature(?GatewayFeature $supportedFeature, bool $isSupported = true): void
    {
        $this->gatewayMock
            ->method('isFeatureSupported')
            ->with($supportedFeature)
            ->willReturn($isSupported);
    }
}
