<?php

namespace Motekar\FlexField\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FlexFieldMigrateCommand extends Command
{
    protected $signature = 'flexfield:migrate';

    protected $description = 'Create or update FlexField tables based on configuration';

    public function handle()
    {
        // Get the FlexField configuration from the config file
        $flexfieldConfig = config('flexfield');

        foreach ($flexfieldConfig as $flexName => $tableDetail) {
            $parentTable = (new $tableDetail['parentClass'])->getTable();
            $tableName = "{$parentTable}_{$flexName}_flex";
            $fields = $tableDetail['fields'];
            if (! $this->tableExists($tableName)) {
                $this->createTable($tableName, $fields, $tableDetail);
            } else {
                $this->addFieldsToTable($tableName, $fields);
            }
        }

        $this->info('FlexField tables created or updated successfully.');

        $this->buildRelationClass();
        $this->info('FlexField relation class built successfully.');
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
        Schema::table($tableName, function (Blueprint $table) use ($fieldName, $attributes, $tableName) {
            $type = $attributes['type'];

            // if (! $this->isValidColumnType($type)) {
            //     $this->error("Invalid column type specified: $type");

            //     return;
            // }

            try {
                $column = $table->{$type}($fieldName);
            } catch (\Throwable $th) {
                $this->error("Invalid column type specified: $type");
                return;
            }

            // Additional field configuration options
            foreach ($attributes as $key => $value) {
                if ($key !== 'type') {
                    try {
                        $column->{$key}($value);
                    } catch (\Exception $e) {
                        $this->error("Error applying column option: $key. Error: ".$e->getMessage());
                    }
                }
            }

            $this->info("Field added: $tableName.$fieldName");
        });
    }

    private function createTable($tableName, $fields, $tableDetail)
    {
        Schema::create($tableName, function (Blueprint $table) use ($fields, $tableDetail) {
            $table->id();

            $parentTable = (new $tableDetail['parentClass'])->getTable();

            $foreignIdColumn = Str::singular($parentTable).'_id';
            $table->foreignId($foreignIdColumn)->constrained($parentTable);

            foreach ($fields as $fieldName => $attributes) {
                $type = $attributes['type'];

                try {
                    $column = $table->{$type}($fieldName);
                } catch (\Throwable $th) {
                    $this->error("Invalid column type specified: $type");
                    return;
                }

                // Additional field configuration options
                foreach ($attributes as $key => $value) {
                    if ($key !== 'type') {
                        try {
                            $column->{$key}($value);
                        } catch (\Exception $e) {
                            $this->error("Error applying column option: $key. Error: ".$e->getMessage());
                        }
                    }
                }
            }

            $table->timestamps();
        });

        $this->info("Table created: $tableName");
    }

    private function buildRelationClass()
    {
        $flexfield = config('flexfield');
        foreach($flexfield as $flexName => $flexDetail) {
            $parentTable = (new $flexDetail['parentClass'])->getTable();

            $tableName = "{$parentTable}_{$flexName}_flex";
            $fields = array_keys($flexDetail['fields']);

            $fillableString = "'" . implode("', '", $fields) . "'";

            $modelContent = $this->generateModelContent(
                Str::studly($tableName),
                $tableName,
                $fillableString,
                Str::singular($tableName),
                $flexDetail['parentClass']
            );

            if (!file_exists(storage_path('flexfield'))) {
                File::makeDirectory(storage_path('flexfield'));
            }

            $classPath = storage_path('flexfield/'.$tableName.'.php');

            if (file_exists($classPath)) {
                unlink($classPath);
            }
            file_put_contents($classPath, $modelContent);
        }
    }

    private function generateModelContent($className, $tableName, $fillableAttributes, $parentName, $parentClass)
    {
        $modelTemplate = <<<EOT
<?php
// namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class $className extends Model
{
    protected \$table = '$tableName';

    protected \$fillable = [
        $fillableAttributes
    ];

    public function $parentName()
    {
        return \$this->belongsTo('$parentClass');
    }
}
EOT;

        return $modelTemplate;
    }
}
