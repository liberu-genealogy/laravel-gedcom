<?php

namespace Asdfx\LaravelGedcom;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations/');
    }
}