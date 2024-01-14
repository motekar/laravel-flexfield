<?php

use Illuminate\Support\Facades\Schema;
use Motekar\FlexField\Tests\TestClasses\TestModel;

use function Pest\Laravel\artisan;

beforeEach(function () {
    // Mock the config file with your flexfield configuration
    config()->set('flexfield', [
        'flexdata' => [
            'parentClass' => TestModel::class,
            'isMany' => false,
            'fields' => [
                'field_name_1' => [
                    'type' => 'string',
                ],
                'field_name_2' => [
                    'type' => 'integer',
                    'default' => 42,
                ],
            ],
        ],
    ]);
});

it('creates tables and fields based on configuration', function () {
    artisan('flexfield:migrate')
        ->expectsOutput('FlexField tables created or updated successfully.')
        ->assertExitCode(0);

    // Assert that tables exist in the database
    $exists = Schema::hasTable('test_models_flexdata_flex');
    expect($exists)->toBeTrue();

    // Assert that fields exist in the tables
    $columnExists = Schema::hasColumns(
        'test_models_flexdata_flex',
        ['field_name_1', 'field_name_2']
    );
    expect($columnExists)->toBeTrue();
});
