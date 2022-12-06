<?php

use ValeSaude\PaymentGatewayClient\Models\Invoice;
use ValeSaude\PaymentGatewayClient\Models\InvoiceItem;
use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

test('getTotalAttribute returns sum of each item price times its quantity', function () {
    // given
    $invoice = Invoice::factory()->create();
    $item1 = InvoiceItem
        ::factory()
        ->for($invoice)
        ->create([
            'price' => new Money(1000),
            'quantity' => 1,
        ]);
    $item2 = InvoiceItem
        ::factory()
        ->for($invoice)
        ->create([
            'price' => new Money(500),
            'quantity' => 2,
        ]);
    $item3 = InvoiceItem
        ::factory()
        ->for($invoice)
        ->create([
            'price' => new Money(2500),
            'quantity' => 3,
        ]);

    // when
    $total = $invoice->total;

    // then
    expect($total->getCents())->toEqual(9500);
});
