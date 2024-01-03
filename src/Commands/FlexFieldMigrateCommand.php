<?php

namespace Motekar\FlexField\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FlexFieldMigrateCommand extends Command
{
    protected $signature = 'flexfield:migrate';

    protected $description = 'Create or update FlexField tables based on configuration';

    public function handle()
    {
        // Get the FlexField configuration from the config file
        $flexfieldConfig = config('flexfield');

        foreach ($flexfieldConfig as $tableName => $fields) {
            if (! $this->tableExists($tableName)) {
                $this->createTable($tableName, $fields);
            } else {
                $this->addFieldsToTable($tableName, $fields);
            }
        }

        $this->info('FlexField tables created or updated successfully.');
    }

    private function tableExists($tableName)
    {
        return Schema::hasTable($tableName);
    }

    private function addFieldsToTable($tableName, $fields)
    {
        foreach ($fields as $fieldName => $attributes) {
            if (! $this->fieldExists($tableName, $fieldName)) {
                $this->addFieldToTable($tableName, $fieldName, $attributes);
            } else {
                $this->info("Field already exists: $tableName.$fieldName");
            }
        }
    }

    private function fieldExists($tableName, $fieldName)
    {
        return Schema::hasColumn($tableName, $fieldName);
    }

    private function addFieldToTable($tableName, $fieldName, $attributes)
    {
        Schema::table($tableName, function (Blueprint $table) use ($fieldName, $attributes) {
            $type = $attributes['type'];

            if (! $this->isValidColumnType($type)) {
                $this->error("Invalid column type specified: $type");

                return;
            }

            $column = $table->{$type}($fieldName);

            // Additional field configuration options
            foreach ($attributes as $key => $value) {
                if ($key !== 'type') {
                    if (! $this->isValidColumnOption($key)) {
                        $this->error("Invalid column option specified: $key");

                        continue;
                    }

                    try {
                        // Use a try-catch block to handle any potential exceptions
                        $column->{$key}($value);
                    } catch (\Exception $e) {
                        $this->error("Error applying column option: $key. Error: ".$e->getMessage());
                    }
                }
            }

            $this->info("Field added: $tableName.$fieldName");
        });
    }

    private function createTable($tableName, $fields)
    {
        Schema::create($tableName, function (Blueprint $table) use ($fields) {
            $table->id();

            foreach ($fields as $fieldName => $attributes) {
                $type = $attributes['type'];

                if (! $this->isValidColumnType($type)) {
                    $this->error("Invalid column type specified: $type");

                    return;
                }

                $column = $table->{$type}($fieldName);

                // Additional field configuration options
                foreach ($attributes as $key => $value) {
                    if ($key !== 'type') {
                        $column->{$key}($value);
                    }
                }
            }

            $table->timestamps();
        });

        $this->info("Table created: $tableName");
    }

    private function isValidColumnType($type)
    {
        $allowedColumnTypes = [
            'bigInteger', 'binary', 'boolean', 'char', 'date', 'dateTime', 'decimal', 'double',
            'enum', 'float', 'geometry', 'geometryCollection', 'increments', 'integer', 'ipAddress',
            'json', 'jsonb', 'lineString', 'longText', 'macAddress', 'mediumIncrements', 'mediumInteger',
            'mediumText', 'morphs', 'multiLineString', 'multiPoint', 'multiPolygon', 'nullableMorphs',
            'nullableTimestamps', 'point', 'polygon', 'rememberToken', 'set', 'smallIncrements',
            'smallInteger', 'softDeletes', 'softDeletesTz', 'string', 'text', 'time', 'timestamp',
            'timestampTz', 'tinyIncrements', 'tinyInteger', 'timestamps', 'timestampsTz', 'uuid',
            'uuidMorphs',
        ];

        return in_array($type, $allowedColumnTypes);
    }

    private function isValidColumnOption($key)
    {
        // Define a list of valid column options
        $validColumnOptions = [
            'nullable', 'default', 'unsigned', 'autoIncrement', 'charset', 'collation',
            // Add more valid options as needed
        ];

        // Check if the specified option is valid
        return in_array($key, $validColumnOptions);
    }
}
