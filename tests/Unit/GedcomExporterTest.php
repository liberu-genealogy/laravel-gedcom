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
