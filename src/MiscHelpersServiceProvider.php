<?php

namespace Daikazu\MiscHelpers;

use Daikazu\MiscHelpers\Commands\PruneLogFilesCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MiscHelpersServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('misc-helpers')
            ->hasCommand(PruneLogFilesCommand::class);
    }
}
