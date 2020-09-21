<?php

namespace GenealogiaWebsite\LaravelGedcom;

use GenealogiaWebsite\LaravelGedcom\Commands\GedcomImporter;
use GenealogiaWebsite\LaravelGedcom\Utils\GedcomParser;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->commands([
            GedcomImporter::class,
        ]);

        $this->app->bind('genealogiawebsite/laravel-gedcom:parser', function () {
            return new GedcomParser();
        });
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations/');
    }
}
