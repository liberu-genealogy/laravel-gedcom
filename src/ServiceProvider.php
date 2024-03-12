<?php

namespace FamilyTree365\LaravelGedcom;

use FamilyTree365\LaravelGedcom\Commands\GedcomImporter;
use FamilyTree365\LaravelGedcom\Utils\GedcomParser;
use \Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public $app;
    public function register()
    {
        $this->app->singleton('FamilyTree365/laravel-gedcom:parser', fn() => new GedcomParser());
        $this->commands(GedcomImporter::class);
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations/');
    }
}
