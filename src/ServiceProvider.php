<?php

namespace FamilyTree365\LaravelGedcom;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use FamilyTree365\LaravelGedcom\Utils\GedcomParser;
use FamilyTree365\LaravelGedcom\Utils\GedcomXParser;

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
                \FamilyTree365\LaravelGedcom\Commands\GedcomXImporter::class,
                \FamilyTree365\LaravelGedcom\Commands\GedcomXImporterOptimized::class,
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

        // Register the GedcomXParser singleton
        $this->app->singleton('gedcomx-parser', function ($app) {
            return new GedcomXParser();
        });

        // Register the facades
        $this->app->alias('gedcom-parser', 'GedcomParser');
        $this->app->alias('gedcomx-parser', 'GedcomXParser');
    }
}