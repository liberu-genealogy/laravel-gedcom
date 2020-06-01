<?php

namespace Asdfx\LaravelGedcom;

use Asdfx\LaravelGedcom\Commands\GedcomImporter;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->commands([
            GedcomImporter::class
        ]);
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations/');
    }
}