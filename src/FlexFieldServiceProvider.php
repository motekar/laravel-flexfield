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
}
