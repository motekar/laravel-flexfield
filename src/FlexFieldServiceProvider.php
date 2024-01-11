<?php

namespace Motekar\FlexField;

use Motekar\FlexField\Commands\FlexFieldMigrateCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FlexFieldServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-flexfield')
            ->hasConfigFile()
            ->hasCommand(FlexFieldMigrateCommand::class);
    }

    public function packageRegistered()
    {
        $flexfield = config('flexfield');
        foreach ($flexfield as $flexName => $flexDetail) {
            $parentTable = (new $flexDetail['parentClass'])->getTable();
            $tableName = "{$parentTable}_{$flexName}_flex";

            $classPath = storage_path('flexfield/'.$tableName.'.php');
            if (file_exists($classPath)) {
                require_once $classPath;
            }
        }
    }
}
