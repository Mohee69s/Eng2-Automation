<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function workflowPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'name' => 'Console Flow',
        'description' => 'Simple console automation flow',
        'nodes' => [
            [
                'id' => 'start-1',
                'type' => 'start',
                'x' => 10,
                'y' => 10,
                'text' => '',
                'color' => '#94a3b8',
            ],
            [
                'id' => 'log-1',
                'type' => 'log',
                'x' => 100,
                'y' => 10,
                'text' => 'hello',
                'color' => '#94a3b8',
            ],
            [
                'id' => 'color-1',
                'type' => 'color',
                'x' => 200,
                'y' => 10,
                'text' => 'styled',
                'color' => '#ef4444',
            ],
        ],
        'edges' => [
            ['from' => 'start-1', 'to' => 'log-1'],
            ['from' => 'log-1', 'to' => 'color-1'],
        ],
    ], $overrides);
}

test('it creates and returns a workflow graph', function () {
    $response = $this->postJson('/api/workflows', workflowPayload());

    $response
        ->assertCreated()
        ->assertJsonPath('data.name', 'Console Flow')
        ->assertJsonCount(3, 'data.nodes')
        ->assertJsonCount(2, 'data.edges')
        ->assertJsonPath('data.nodes.0.executionOrder', 1);
});

test('it updates a workflow graph', function () {
    $created = $this->postJson('/api/workflows', workflowPayload())->json('data');

    $updatePayload = workflowPayload([
        'name' => 'Updated Flow',
        'edges' => [
            ['from' => 'start-1', 'to' => 'color-1'],
            ['from' => 'color-1', 'to' => 'log-1'],
        ],
    ]);

    $response = $this->putJson('/api/workflows/'.$created['id'], $updatePayload);

    $response
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Flow')
        ->assertJsonPath('data.edges.0.from', 'start-1')
        ->assertJsonPath('data.edges.1.to', 'log-1');
});

test('it lists and deletes workflows', function () {
    $created = $this->postJson('/api/workflows', workflowPayload())->json('data');

    $this->getJson('/api/workflows')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $this->deleteJson('/api/workflows/'.$created['id'])
        ->assertNoContent();

    $this->getJson('/api/workflows')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});
