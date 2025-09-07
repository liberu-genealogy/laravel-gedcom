<?php

namespace FamilyTree365\LaravelGedcom\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * GedcomXParserFacade provides a convenient way to access the GedcomXParser
 * 
 * @method static void parse(mixed $conn, string $filename, string $slug, bool $progressBar = null, mixed $tenant = null, array $channel = ['name' => 'gedcomx-progress', 'eventName' => 'newMessage'])
 * @method static bool isGedcomXFile(string $filename)
 * 
 * @see \FamilyTree365\LaravelGedcom\Utils\GedcomXParser
 */
class GedcomXParserFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gedcomx-parser';
    }
}