<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\App;
use FamilyTree365\LaravelGedcom\Utils\GedcomParser;

class ServiceProviderTest extends TestCase
{
    /**
     * Test the singleton registration of the GedcomParser.
     *
     * @return void
     */
    public function testGedcomParserSingletonRegistration()
    {
        $parserInstanceOne = app('FamilyTree365/laravel-gedcom:parser');
        $parserInstanceTwo = app('FamilyTree365/laravel-gedcom:parser');

        $this->assertSame($parserInstanceOne, $parserInstanceTwo);
    }
}

