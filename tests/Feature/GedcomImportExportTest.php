&lt;?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use FamilyTree365\LaravelGedcom\Utils\GedcomImporter;
use FamilyTree365\LaravelGedcom\Utils\GedcomExporter;

class GedcomImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function testGedcomImportExportIsLossless()
    {
        Storage::fake('local');

        $mockGedcomContent = "0 HEAD\n1 SOUR LaravelGedcom\n1 GEDC\n2 VERS 5.5.1\n0 TRLR";
        $mockFilename = 'test_import_export.ged';

        Storage::disk('local')->put($mockFilename, $mockGedcomContent);

        GedcomImporter::importData($mockFilename);
        GedcomExporter::exportData($mockFilename);

        $exportedGedcomContent = Storage::disk('local')->get($mockFilename);

        $this->assertEquals($mockGedcomContent, $exportedGedcomContent, 'The exported GEDCOM should match the imported GEDCOM content.');
    }
}
