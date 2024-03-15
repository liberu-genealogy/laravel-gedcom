<![CDATA[
<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Commands\GedcomImporter;
use FamilyTree365\LaravelGedcom\Facades\GedcomParserFacade;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Mockery;

class GedcomImporterTest extends TestCase
{
    public function testImportingDataFromWellFormedGedcomFile()
    {
        Storage::fake('local');
        Storage::disk('local')->put('well_formed.ged', 'Well-formed GEDCOM content');

        GedcomParserFacade::shouldReceive('parse')
            ->once()
            ->with('mysql', 'storage/app/well_formed.ged', true, true)
            ->andReturn(true);

        $this->artisan('gedcom:import', ['filename' => 'storage/app/well_formed.ged'])
             ->assertExitCode(0);
    }

    public function testImportingDataFromMalformedGedcomFile()
    {
        Storage::fake('local');
        Storage::disk('local')->put('malformed.ged', 'Malformed GEDCOM content');
/**
 * Test class for GedcomImporter.
 * 
 * This class is designed to test the functionality of the GedcomImporter command,
 * ensuring that it correctly handles well-formed and malformed GEDCOM files,
 * as well as various file structures.
 */
        GedcomParserFacade::shouldReceive('parse')
            ->once()
            ->with('mysql', 'storage/app/malformed.ged', true, true)
            ->andThrow(new \Exception('Parsing error'));

        $this->artisan('gedcom:import', ['filename' => 'storage/app/malformed.ged'])
             ->assertExitCode(1);
    }

    public function testImportingDataWithVariousGedcomFileStructures()
    {
        Storage::fake('local');
        $files = ['structure1.ged', 'structure2.ged', 'structure3.ged'];

        foreach ($files as $file) {
            Storage::disk('local')->put($file, "GEDCOM content for $file");

            GedcomParserFacade::shouldReceive('parse')
                ->with('mysql', "storage/app/$file", true, true)
                ->andReturn(true);
        }

        foreach ($files as $file) {
            $this->artisan('gedcom:import', ['filename' => "storage/app/$file"])
                 ->assertExitCode(0);
        }
    }
}
]]>
