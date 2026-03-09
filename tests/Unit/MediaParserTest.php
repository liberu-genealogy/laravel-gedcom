<?php

namespace Tests\Unit;

use Tests\TestCase;
use FamilyTree365\LaravelGedcom\Utils\Importer\MediaParser;
use Illuminate\Support\Facades\DB;
use Mockery;

class MediaParserTest extends TestCase
{
    protected $mediaParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockDatabase();
        $this->mediaParser = new MediaParser('default');
    }

    private function mockDatabase()
    {
        DB::shouldReceive('beginTransaction')->andReturnSelf();
        DB::shouldReceive('commit')->andReturnSelf();
        DB::shouldReceive('rollBack')->andReturnSelf();
    }

    public function testMediaParserCanBeInstantiated()
    {
        $this->assertInstanceOf(MediaParser::class, $this->mediaParser);
    }

    public function testParseMediaObjectsWithEmptyArray()
    {
        // Should not throw an exception with empty array
        $this->mediaParser->parseMediaObjects([]);
        $this->assertTrue(true);
    }

    public function testParseMediaObjectsMethodExists()
    {
        $this->assertTrue(method_exists(MediaParser::class, 'parseMediaObjects'));
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}