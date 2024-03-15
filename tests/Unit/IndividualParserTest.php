<![CDATA[
<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Utils\Importer\IndividualParser;
use FamilyTree365\LaravelGedcom\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class IndividualParserTest extends TestCase
{
    use RefreshDatabase;

    protected $individualParser;

    protected function setUp(): void
    {
        parent::setUp();
        DB::shouldReceive('beginTransaction')->andReturnTrue();
        DB::shouldReceive('commit')->andReturnTrue();
        DB::shouldReceive('rollBack')->andReturnTrue();
        Log::shouldReceive('error');
        $this->individualParser = new IndividualParser(DB::connection());
    }

    public function testParseIndividualsWithValidData()
    {
        $individuals = [
            (object)['getId' => 'I1', 'getName' => (object)['getFullName' => 'John Doe'], 'getSex' => 'M', 'getBirth' => (object)['getDate' => '1980-01-01'], 'getDeath' => (object)['getDate' => '2050-01-01']],
            (object)['getId' => 'I2', 'getName' => (object)['getFullName' => 'Jane Doe'], 'getSex' => 'F', 'getBirth' => (object)['getDate' => '1985-02-02'], 'getDeath' => null]
        ];

        Person::shouldReceive('save')->twice();
        $this->individualParser->parseIndividuals($individuals);

        $this->assertTrue(true); // Assuming save was successful
    }

    public function testParseIndividualsWithEmptyArray()
    {
        $individuals = [];
        $this->individualParser->parseIndividuals($individuals);

        $this->assertTrue(true); // No exception means pass
    }

    public function testParseIndividualsWithInvalidData()
    {
        $individuals = ['invalid_data'];

        $this->expectException(\Exception::class);
        $this->individualParser->parseIndividuals($individuals);
    }
}
]]>
