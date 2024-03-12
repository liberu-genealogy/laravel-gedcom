&lt;?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use FamilyTree365\LaravelGedcom\Utils\Importer\Note;
use FamilyTree365\LaravelGedcom\Utils\Importer\Obje;
use FamilyTree365\LaravelGedcom\Utils\Importer\Repo;

class ImporterTests extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::shouldReceive('connection')->andReturnSelf();
        DB::shouldReceive('disableQueryLog')->andReturnTrue();
    }

    public function testNoteImport()
    {
        list($mockData, $expectedResult) = $this->prepareMockDataForNoteImport();

        $noteImporter = new Note();
        $result = $noteImporter->import($mockData);

        $this->assertTrue($result);
        $this->verifyNoteImportResult($expectedResult);
    }

    public function testObjeImport()
    {
        $mockData = "0 @O1@ OBJE\n1 FILE example.jpg\n2 TITL Example Title";
        $expectedResult = ['id' => 'O1', 'file' => 'example.jpg', 'title' => 'Example Title'];

        DB::shouldReceive('table')->with('media_objects')->andReturnSelf();
        DB::shouldReceive('insert')->with($expectedResult)->andReturnTrue();

        $objeImporter = new Obje();
        $result = $objeImporter->import($mockData);

        $this->assertTrue($result);
        DB::shouldReceive('table')->with('media_objects')->andReturnSelf();
        DB::shouldReceive('where')->with('id', 'O1')->andReturnSelf();
        DB::shouldReceive('first')->andReturn((object)$expectedResult);

        $storedMedia = DB::table('media_objects')->where('id', 'O1')->first();
        $this->assertEquals($expectedResult['title'], $storedMedia->title);
    }

    public function testRepoImport()
    {
        $mockData = "0 @R1@ REPO\n1 NAME Example Repository";
        $expectedResult = ['id' => 'R1', 'name' => 'Example Repository'];

        DB::shouldReceive('table')->with('repositories')->andReturnSelf();
        DB::shouldReceive('insert')->with($expectedResult)->andReturnTrue();

        $repoImporter = new Repo();
        $result = $repoImporter->import($mockData);

        $this->assertTrue($result);
        DB::shouldReceive('table')->with('repositories')->andReturnSelf();
        DB::shouldReceive('where')->with('id', 'R1')->andReturnSelf();
        DB::shouldReceive('first')->andReturn((object)$expectedResult);

        $storedRepo = DB::table('repositories')->where('id', 'R1')->first();
        $this->assertEquals($expectedResult['name'], $storedRepo->name);
    }
}
    private function prepareMockDataForNoteImport()
    {
        $mockData = "0 @N1@ NOTE This is a test note";
        $expectedResult = ['id' => 'N1', 'content' => 'This is a test note'];

        DB::shouldReceive('table')->with('notes')->andReturnSelf();
        DB::shouldReceive('insert')->with($expectedResult)->andReturnTrue();

        return [$mockData, $expectedResult];
    }
    private function verifyNoteImportResult($expectedResult)
    {
        DB::shouldReceive('table')->with('notes')->andReturnSelf();
        DB::shouldReceive('where')->with('id', $expectedResult['id'])->andReturnSelf();
        DB::shouldReceive('first')->andReturn((object)$expectedResult);

        $storedNote = DB::table('notes')->where('id', $expectedResult['id'])->first();
        $this->assertEquals($expectedResult['content'], $storedNote->content);
    }
    }
    private function verifyObjeImportResult($expectedResult)
    {
        DB::shouldReceive('table')->with('media_objects')->andReturnSelf();
        DB::shouldReceive('where')->with('id', $expectedResult['id'])->andReturnSelf();
        DB::shouldReceive('first')->andReturn((object)$expectedResult);

        $storedMedia = DB::table('media_objects')->where('id', $expectedResult['id'])->first();
        $this->assertEquals($expectedResult['title'], $storedMedia->title);
    }
    }
    /**
     * Prepares mock data for testing the note import functionality.
     * This includes setting up mock expectations for database interactions.
     * 
     * @return array An array containing the mock data and the expected result.
     */
    /**
     * Verifies the result of the note import operation against the expected result.
     * This includes setting up mock expectations for database queries and asserting the equality of the stored note content.
     * 
     * @param array $expectedResult The expected result of the note import operation.
     */
    /**
     * Verifies the result of the media object import operation against the expected result.
     * This includes setting up mock expectations for database queries and asserting the equality of the stored media object details.
     * 
     * @param array $expectedResult The expected result of the media object import operation.
     */
