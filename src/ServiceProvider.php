<?php

namespace Asdfx\LaravelGedcom;

use Asdfx\LaravelGedcom\Commands\GedcomImporter;
use Asdfx\LaravelGedcom\Utils\GedcomParser;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->commands([
            GedcomImporter::class
        ]);

        $this->app->bind('asdfx/laravel-gedcom:parser', function () {
            return new GedcomParser();
        });
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations/');
    }
}