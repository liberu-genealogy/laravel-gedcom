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
        $this->setupExportTestEnvironment('test');
        $this->verifyExportedFileExists('test.GED');
    }

    public function testExportingDataWithMockDatabaseRecords()
    {
        $this->setupExportTestEnvironment('mockData');
        $this->verifyExportedFileExists('mockData.GED');
    }

    public function testHandlingOfFileWriteErrors()
    {
        Storage::shouldReceive('makeDirectory')->andThrow(new \Exception('Failed to create directory'));
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to create directory');

        $this->artisan('gedcom:export', ['filename' => 'errorCase'])
             ->assertExitCode(1);
    private function setupExportTestEnvironment($filename)
    {
        Storage::fake('local');
        $this->artisan('gedcom:export', ['filename' => $filename])
             ->assertExitCode(0);
    }

    private function verifyExportedFileExists($filename)
    {
        Storage::disk('local')->assertExists('public/gedcom/exported/' . $filename);
    }
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
    /**
     * Sets up the environment for exporting a GEDCOM file.
     * This includes faking the local storage and asserting the exit code of the artisan command.
     * 
     * @param string $filename The name of the file to be exported.
     */
    /**
     * Verifies that the exported GEDCOM file exists in the specified storage disk.
     * 
     * @param string $filename The name of the exported file to check.
     */
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
