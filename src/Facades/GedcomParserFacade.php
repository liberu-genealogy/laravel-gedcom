<?php

namespace FamilyTree365\LaravelGedcom\Facades;

use Illuminate\Support\Facades\Facade;

class GedcomParserFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'FamilyTree365/laravel-gedcom:parser';
    }
}
