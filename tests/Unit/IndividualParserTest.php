<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Utils\Importer\IndividualParser;
use FamilyTree365\LaravelGedcom\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Orchestra\Testbench\TestCase;
use Mockery;

class IndividualParserTest extends TestCase
{
    use RefreshDatabase;

    protected $individualParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockDatabase();
        $this->individualParser = new IndividualParser(DB::connection());
    }

    private function mockDatabase()
    {
        DB::shouldReceive('beginTransaction')->andReturnSelf();
        DB::shouldReceive('commit')->andReturnSelf();
        DB::shouldReceive('rollBack')->andReturnSelf();
        Log::shouldReceive('error')->andReturnNull();
    }

    public function testParseIndividualsWithValidData()
    {
        $individuals = [
            (object)[
                'getId' => 'I1',
                'getName' => (object)['getFullName' => 'John Doe'],
                'getSex' => 'M',
                'getBirth' => (object)['getDate' => '1980-01-01'],
                'getDeath' => (object)['getDate' => '2050-01-01']
            ]
        ];

        Person::shouldReceive('create')->once()->andReturn(new Person());
        
        $result = $this->individualParser->parseIndividuals($individuals);
        $this->assertTrue($result);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

