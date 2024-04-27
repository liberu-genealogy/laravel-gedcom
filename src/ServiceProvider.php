<?php

namespace FamilyTree365\LaravelGedcom;

use FamilyTree365\LaravelGedcom\Commands\GedcomImporter;
use FamilyTree365\LaravelGedcom\Utils\GedcomParser;
use \Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public $app;
    /**
     * Register service provider bindings and commands.
     * 
     * This method binds the GedcomParser singleton into the Laravel service container
     * and registers the GedcomImporter command for use within the application.
     * It does not accept any inputs and has no return value.
     */
    public function register()
    {
        $this->app->singleton('FamilyTree365/laravel-gedcom:parser', fn() => new GedcomParser());
        $this->commands(GedcomImporter::class);
    }

    /**
     * Load migration files from a specified directory.
     * 
     * This method is called when the service provider is booted, ensuring that
     * the migration files are loaded into the Laravel application. It does not
     * accept any inputs and has no return value.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations/');
    }
}
