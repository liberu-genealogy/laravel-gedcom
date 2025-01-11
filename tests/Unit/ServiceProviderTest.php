<?php

namespace Tests\Unit;

use Tests\TestCase;
use FamilyTree365\LaravelGedcom\Utils\GedcomParser;

class ServiceProviderTest extends TestCase
{
    public function testGedcomParserSingletonRegistration()
    {
        $this->app->singleton('FamilyTree365/laravel-gedcom:parser', function() {
            return new GedcomParser();
        });
        
        $parserInstanceOne = app('FamilyTree365/laravel-gedcom:parser');
        $parserInstanceTwo = app('FamilyTree365/laravel-gedcom:parser');

        $this->assertSame($parserInstanceOne, $parserInstanceTwo);
    }
}

