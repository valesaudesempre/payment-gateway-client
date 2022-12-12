<?php

use ValeSaude\PaymentGatewayClient\ValueObjects\JsonObject;

test('get correctly handles both existing and non existing keys', function () {
    // given
    $instance = new JsonObject([
        'key1' => 1,
        'key2' => [
            'key21' => 'some value',
        ],
    ]);

    // then
    expect($instance->get('key1'))->toBe(1)
        ->and($instance->get('key2'))->toBe(['key21' => 'some value'])
        ->and($instance->get('key2.key21'))->toBe('some value')
        ->and($instance->get('key1.key11'))->toBeNull()
        ->and($instance->get('key2.key22'))->toBeNull()
        ->and($instance->get('key3', 'default value'))->toBe('default value');
});

test('set returns a new JsonObject instance with given key set', function () {
    // given
    $instance1 = new JsonObject([]);

    // when
    $instance2 = $instance1->set('key1', 1);
    $instance3 = $instance2->set('key1', 2);

    // then
    expect($instance1->toArray())->toBe([])
        ->and($instance2->toArray())->toBe(['key1' => 1])
        ->and($instance3->toArray())->toBe(['key1' => 2]);
});

test('merge returns a new JsonObject instance with merged keys', function () {
    // given
    $instance1 = new JsonObject([
        'key1' => 1,
        'key2' => [
            'key21' => 2,
        ],
        'key3' => 3,
    ]);

    // when
    $instance2 = $instance1->merge([
        'key1' => 2,
        'key2' => [
            'key22' => 4,
        ],
        'key4' => 5,
    ]);

    // then
    expect($instance1->equals($instance2))->toBeFalse()
        ->and($instance2->toArray())->toBe([
            'key1' => 2,
            'key2' => [
                'key22' => 4,
            ],
            'key3' => 3,
            'key4' => 5,
        ]);
});
