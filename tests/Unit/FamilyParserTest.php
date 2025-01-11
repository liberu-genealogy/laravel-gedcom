<?php

namespace Tests\Unit;

use Tests\TestCase;
use FamilyTree365\LaravelGedcom\Utils\Importer\FamilyParser;
use FamilyTree365\LaravelGedcom\Models\Family;
use Illuminate\Support\Facades\DB;
use Mockery;

class FamilyParserTest extends TestCase
{
    protected $familyParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockDatabase();
        $this->familyParser = new FamilyParser(DB::connection());
    }

    private function mockDatabase()
    {
        DB::shouldReceive('beginTransaction')->andReturnSelf();
        DB::shouldReceive('commit')->andReturnSelf();
        DB::shouldReceive('rollBack')->andReturnSelf();
    }

    public function testParseFamiliesWithValidData()
    {
        $families = [
            (object)[
                'getId' => 'F1',
                'getMarr' => (object)[
                    'getDate' => '1990-01-01',
                    'getPlac' => 'Place1'
                ]
            ]
        ];

        Family::shouldReceive('create')->once()->andReturn(new Family());
        
        $result = $this->familyParser->parseFamilies($families);
        $this->assertTrue($result);
    }
}

