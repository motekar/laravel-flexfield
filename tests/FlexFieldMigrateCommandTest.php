<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseSchemaHas;
use function Pest\Laravel\assertDatabaseTableHas;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    // Mock the config file with your flexfield configuration
    config()->set('flexfield', [
        'flexfield_table_1' => [
            'field_name_1' => [
                'type' => 'string',
            ],
            'field_name_2' => [
                'type' => 'integer',
                'default' => 42,
            ],
        ],
    ]);
});

it('creates tables and fields based on configuration', function () {
    artisan('flexfield:migrate')
        ->expectsOutput('FlexField tables created or updated successfully.')
        ->assertExitCode(0);

    // Assert that tables exist in the database
    assertDatabaseSchemaHas('flexfield_table_1', 'default');

    // Assert that fields exist in the tables
    assertDatabaseTableHas('flexfield_table_1', ['field_name_1' => '']);
    assertDatabaseTableHas('flexfield_table_1', ['field_name_2' => 42]);
});
