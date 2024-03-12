<![CDATA[
<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Commands\GedcomExporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class GedcomExporterTest extends TestCase
{
    public function testExportingDataCreatesDirectoryIfNeeded()
    {
        Storage::fake('local');
        $this->artisan('gedcom:export', ['filename' => 'test'])
             ->assertExitCode(0);
        Storage::disk('local')->assertExists('public/gedcom/exported/test.GED');
    }

    public function testExportingDataWithMockDatabaseRecords()
    {
        DB::shouldReceive('table')
          ->with('subms')
          ->andReturnSelf()
          ->shouldReceive('join')
          ->with('addrs', 'addrs.id', '=', 'subms.addr_id')
          ->andReturnSelf()
          ->shouldReceive('select')
          ->andReturnSelf()
          ->shouldReceive('get')
          ->andReturn(collect([
              (object)['name' => 'John Doe', 'adr1' => '123 Main St', 'city' => 'Anytown', 'stae' => 'CA', 'post' => '12345', 'ctry' => 'USA', 'phon' => '555-1234'],
          ]));

        View::shouldReceive('make')
            ->with('stubs.ged', \Mockery::any())
            ->andReturnSelf()
            ->shouldReceive('render')
            ->andReturn("0 @I1@ INDI\n1 NAME John Doe\n1 ADDR 123 Main St\n2 CITY Anytown\n2 STAE CA\n2 POST 12345\n2 CTRY USA\n1 PHON 555-1234\n");

        $this->artisan('gedcom:export', ['filename' => 'mockData'])
             ->assertExitCode(0);

        Storage::disk('local')->assertExists('public/gedcom/exported/mockData.GED');
        $this->assertStringContainsString("0 @I1@ INDI", Storage::disk('local')->get('public/gedcom/exported/mockData.GED'));
    }

    public function testHandlingOfFileWriteErrors()
    {
        Storage::shouldReceive('makeDirectory')->andThrow(new \Exception('Failed to create directory'));
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to create directory');

        $this->artisan('gedcom:export', ['filename' => 'errorCase'])
             ->assertExitCode(1);
    }
}
]]>
    /**
     * @dataProvider exportDataProvider
     */
    public function testExportingData($data, $expected)
    {
        DB::shouldReceive('table')->andReturnSelf();
        View::shouldReceive('make')->andReturnSelf()->shouldReceive('render')->andReturn($expected);

        $this->artisan('gedcom:export', ['filename' => 'exportTest'])->assertExitCode(0);

        Storage::disk('local')->assertExists('public/gedcom/exported/exportTest.GED');
        $this->assertStringContainsString($expected, Storage::disk('local')->get('public/gedcom/exported/exportTest.GED'));
    }

    public function exportDataProvider()
    {
        return [
            'individuals' => [
                ['type' => 'individuals', 'data' => ['name' => 'John Doe']],
                "0 @I1@ INDI\n1 NAME John Doe\n"
            ],
            'families' => [
                ['type' => 'families', 'data' => ['id' => 'F1']],
                "0 @F1@ FAM\n"
            ],
            'notes' => [
                ['type' => 'notes', 'data' => ['content' => 'Note for individual']],
                "0 @N1@ NOTE Note for individual\n"
            ],
            'media' => [
                ['type' => 'media_objects', 'data' => ['title' => 'Photo of John Doe']],
                "0 @M1@ OBJE\n1 TITL Photo of John Doe\n"
            ],
        ];
    }
