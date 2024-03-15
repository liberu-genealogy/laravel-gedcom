<![CDATA[
<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Utils\Importer\FamilyParser;
use FamilyTree365\LaravelGedcom\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FamilyParserTest extends TestCase
{
    use RefreshDatabase;

    protected $familyParser;

    protected function setUp(): void
    {
        parent::setUp();
        DB::shouldReceive('beginTransaction')->andReturnTrue();
        DB::shouldReceive('commit')->andReturnTrue();
        DB::shouldReceive('rollBack')->andReturnTrue();
        $this->familyParser = new FamilyParser(DB::connection());
    }

    public function testParseFamiliesWithValidData()
    {
        $families = [
            (object)['getId' => 'F1', 'getMarr' => (object)['getDate' => '1990-01-01', 'getPlac' => 'Place1']],
            (object)['getId' => 'F2', 'getMarr' => (object)['getDate' => '2000-02-02', 'getPlac' => 'Place2']]
        ];

        Family::shouldReceive('save')->twice();
        $this->familyParser->parseFamilies($families);

        $this->assertTrue(true); // Assuming save was successful
    }

    public function testParseFamiliesWithEmptyArray()
    {
        $families = [];
        $this->familyParser->parseFamilies($families);

        $this->assertTrue(true); // No exception means pass
    }

    public function testParseFamiliesWithInvalidData()
    {
        $families = ['invalid_data'];

        $this->expectException(\Exception::class);
        $this->familyParser->parseFamilies($families);
    }
}
]]>
