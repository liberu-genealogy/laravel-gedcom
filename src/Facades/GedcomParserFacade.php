<?php

namespace FamilyTree365\LaravelGedcom\Facades;

use Illuminate\Support\Facades\Facade;

class GedcomParserFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gedcom-parser';
    }
}