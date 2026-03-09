<?php

namespace Tests\Unit;

use Tests\TestCase;
use FamilyTree365\LaravelGedcom\Utils\Importer\FamilyParser;
use Illuminate\Support\Facades\DB;
use Mockery;

class FamilyParserTest extends TestCase
{
    protected $familyParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockDatabase();
        $this->familyParser = new FamilyParser('default');
    }

    private function mockDatabase()
    {
        DB::shouldReceive('beginTransaction')->andReturnSelf();
        DB::shouldReceive('commit')->andReturnSelf();
        DB::shouldReceive('rollBack')->andReturnSelf();
    }

    public function testFamilyParserCanBeInstantiated()
    {
        $this->assertInstanceOf(FamilyParser::class, $this->familyParser);
    }

    public function testParseFamiliesWithEmptyArray()
    {
        // parseFamilies with empty array should not throw an exception
        $this->familyParser->parseFamilies([]);
        $this->assertTrue(true);
    }

    public function testParseFamiliesMethodExists()
    {
        $this->assertTrue(method_exists(FamilyParser::class, 'parseFamilies'));
    }
}

