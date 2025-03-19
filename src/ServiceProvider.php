<?php

namespace FamilyTree365\LaravelGedcom;

use FamilyTree365\LaravelGedcom\Commands\GedcomImporter;
use FamilyTree365\LaravelGedcom\Commands\GedcomExporter;
use FamilyTree365\LaravelGedcom\Utils\GedcomParser;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        // Register core services
        $this->app->singleton('FamilyTree365/laravel-gedcom:parser', function ($app) {
            return new GedcomParser();
        });

        // Register commands
        $this->commands([
            GedcomImporter::class,
            GedcomExporter::class
        ]);

        // Register required Laravel services
        $this->app->singleton('filesystem', function ($app) {
            return $app['Illuminate\Filesystem\FilesystemManager'];
        });

        $this->app->singleton('cache', function ($app) {
            return $app['Illuminate\Cache\CacheManager'];
        });

        $this->app->singleton('db', function ($app) {
            return $app['Illuminate\Database\DatabaseManager'];
        });
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations/');
    }
}