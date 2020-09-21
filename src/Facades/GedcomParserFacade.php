<?php

namespace GenealogiaWebsite\LaravelGedcom\Facades;

use Illuminate\Support\Facades\Facade;

class GedcomParserFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'genealogiawebsite/laravel-gedcom:parser';
    }
}
