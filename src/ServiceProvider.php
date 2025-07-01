<?php

namespace FamilyTree365\LaravelGedcom;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use FamilyTree365\LaravelGedcom\Utils\GedcomParser;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \FamilyTree365\LaravelGedcom\Commands\GedcomImporter::class,
                \FamilyTree365\LaravelGedcom\Commands\GedcomExporter::class,
            ]);
        }

        $this->loadMigrationsFrom(__DIR__.'/migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Register the GedcomParser singleton
        $this->app->singleton('gedcom-parser', function ($app) {
            return new GedcomParser();
        });

        // Register the facade
        $this->app->alias('gedcom-parser', 'GedcomParser');
    }
}