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
        $mockData = "0 @N1@ NOTE This is a test note";
        $expectedResult = ['id' => 'N1', 'content' => 'This is a test note'];

        DB::shouldReceive('table')->with('notes')->andReturnSelf();
        DB::shouldReceive('insert')->with($expectedResult)->andReturnTrue();

        $noteImporter = new Note();
        $result = $noteImporter->import($mockData);

        $this->assertTrue($result);
        DB::shouldReceive('table')->with('notes')->andReturnSelf();
        DB::shouldReceive('where')->with('id', 'N1')->andReturnSelf();
        DB::shouldReceive('first')->andReturn((object)$expectedResult);

        $storedNote = DB::table('notes')->where('id', 'N1')->first();
        $this->assertEquals($expectedResult['content'], $storedNote->content);
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
