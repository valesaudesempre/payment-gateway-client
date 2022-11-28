<?php

use function Pest\Laravel\postJson;

test('it returns 404 when gateway cannot be resolved', function () {
    // when
    $response = postJson(route('webhooks.gateway', 'invalid-gateway'));

    // then
    $response->assertStatus(404)
        ->assertSeeText('Gateway settings not found.');
});

// TODO: Implementar testes para demais tipos de resposta do controller
