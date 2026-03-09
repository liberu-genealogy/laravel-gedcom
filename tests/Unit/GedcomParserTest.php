<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Utils\GedcomParser;
use Tests\TestCase;
use Mockery;

class GedcomParserTest extends TestCase
{
    public function testGedcomParserCanBeInstantiated()
    {
        $parser = new GedcomParser();
        $this->assertInstanceOf(GedcomParser::class, $parser);
    }

    public function testGedcomParserHasParseMethod()
    {
        $this->assertTrue(method_exists(GedcomParser::class, 'parse'));
    }

    public function testGedcomParserHasPersonIdsProperty()
    {
        $parser = new GedcomParser();
        $this->assertIsArray($parser->person_ids);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}