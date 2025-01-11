<?php

namespace Tests\Unit;

use Tests\TestCase;
use FamilyTree365\LaravelGedcom\Commands\GedcomImporter;
use FamilyTree365\LaravelGedcom\Facades\GedcomParserFacade;
use Illuminate\Support\Facades\Storage;
use Mockery;

class GedcomImporterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function testImportWithValidFile()
    {
        Storage::disk('local')->put('test.ged', 'Valid GEDCOM content');

        GedcomParserFacade::shouldReceive('parse')
            ->once()
            ->andReturn(true);

        $importer = new GedcomImporter();
        $result = $importer->handle('test.ged');
        
        $this->assertTrue($result);
    }
}
