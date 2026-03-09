<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Utils\Importer\IndividualParser;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;

class IndividualParserTest extends TestCase
{
    protected $individualParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockDatabase();
        $this->individualParser = new IndividualParser('default');
    }

    private function mockDatabase()
    {
        DB::shouldReceive('beginTransaction')->andReturnSelf();
        DB::shouldReceive('commit')->andReturnSelf();
        DB::shouldReceive('rollBack')->andReturnSelf();
        Log::shouldReceive('error')->andReturnNull();
    }

    public function testParseIndividualsWithEmptyArray()
    {
        $result = $this->individualParser->parseIndividuals([]);
        $this->assertTrue($result);
    }

    public function testIndividualParserCanBeInstantiated()
    {
        $this->assertInstanceOf(IndividualParser::class, $this->individualParser);
    }

    public function testParseIndividualsMethodExists()
    {
        $this->assertTrue(method_exists(IndividualParser::class, 'parseIndividuals'));
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

