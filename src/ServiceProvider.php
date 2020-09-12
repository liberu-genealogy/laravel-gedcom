<?php

namespace ModularSoftware\LaravelGedcom;

use ModularSoftware\LaravelGedcom\Commands\GedcomImporter;
use ModularSoftware\LaravelGedcom\Utils\GedcomParser;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->commands([
            GedcomImporter::class
        ]);

        $this->app->bind('modularsoftware/laravel-gedcom:parser', function () {
            return new GedcomParser();
        });
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations/');
    }
}