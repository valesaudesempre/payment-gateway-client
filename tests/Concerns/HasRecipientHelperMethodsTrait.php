<?php

namespace ValeSaude\PaymentGatewayClient\Tests\Concerns;

use ValeSaude\PaymentGatewayClient\Enums\DocumentType;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\BankAccountType;
use ValeSaude\PaymentGatewayClient\Recipient\RecipientDTO;
use ValeSaude\PaymentGatewayClient\Recipient\RepresentativeDTO;
use ValeSaude\PaymentGatewayClient\Tests\TestCase;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\Bank;
use ValeSaude\PaymentGatewayClient\ValueObjects\BankAccount;
use ValeSaude\PaymentGatewayClient\ValueObjects\Document;
use ValeSaude\PaymentGatewayClient\ValueObjects\JsonObject;
use ValeSaude\PaymentGatewayClient\ValueObjects\Phone;
use ValeSaude\PaymentGatewayClient\ValueObjects\ZipCode;

/**
 * @mixin TestCase
 */
trait HasRecipientHelperMethodsTrait
{
    public function createRecipientDTO(): RecipientDTO
    {
        return new RecipientDTO(
            $this->faker->name(),
            new Document($this->faker->cnpj(), DocumentType::CNPJ()),
            new Address(
                new ZipCode($this->faker->numerify('########')),
                $this->faker->streetName(),
                $this->faker->buildingNumber(),
                $this->faker->word(),
                $this->faker->city(),
                $this->faker->lexify('??'),
            ),
            new Phone($this->faker->numerify('###########')),
            new BankAccount(
                new Bank($this->faker->randomKey(Bank::BANKS)),
                $this->faker->numerify('####'),
                $this->faker->numerify('#'),
                $this->faker->numerify('########'),
                $this->faker->numerify('#'),
                BankAccountType::CHECKING()
            ),
            true,
            JsonObject::empty(),
            new RepresentativeDTO(
                $this->faker->name(),
                new Document($this->faker->cnpj(), DocumentType::CNPJ())
            )
        );
    }
}
