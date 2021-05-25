<?php

namespace FamilyTree365\LaravelGedcom;

use FamilyTree365\LaravelGedcom\Commands\GedcomImporter;
use FamilyTree365\LaravelGedcom\Utils\GedcomParser;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->commands([
            GedcomImporter::class,
        ]);

        $this->app->bind('FamilyTree365/laravel-gedcom:parser', function () {
            return new GedcomParser();
        });
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations/');
    }
}
