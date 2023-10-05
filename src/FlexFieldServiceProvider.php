<?php

namespace Motekar\FlexField;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Motekar\FlexField\Commands\FlexFieldCommand;

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
            ->hasViews()
            ->hasMigration('create_laravel-flexfield_table')
            ->hasCommand(FlexFieldCommand::class);
    }
}
