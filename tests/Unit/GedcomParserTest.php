&lt;?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Utils\GedcomParser;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GedcomParserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::shouldReceive('connection')->andReturnSelf();
        DB::shouldReceive('disableQueryLog')->andReturnTrue();
    }

    public function testParseIndividualRecords()
    {
        $filename = __DIR__ . '/../Fixtures/individuals.ged';
        $parser = new GedcomParser();
        $parser->parse(DB::connection(), $filename, 'test-slug', false);

        $individuals = DB::table('individuals')->get();
        $this->assertCount(5, $individuals);
        $this->assertEquals('John Doe', $individuals->first()->name);
    }

    public function testParseFamilyRecords()
    {
        $filename = __DIR__ . '/../Fixtures/families.ged';
        $parser = new GedcomParser();
        $parser->parse(DB::connection(), $filename, 'test-slug', false);

        $families = DB::table('families')->get();
        $this->assertCount(2, $families);
        $this->assertEquals('F1', $families->first()->id);
    }

    public function testParseNotes()
    {
        $filename = __DIR__ . '/../Fixtures/notes.ged';
        $parser = new GedcomParser();
        $parser->parse(DB::connection(), $filename, 'test-slug', false);

        $notes = DB::table('notes')->get();
        $this->assertCount(3, $notes);
        $this->assertStringContainsString('Note for individual', $notes->first()->content);
    }

    public function testParseMediaObjects()
    {
        $filename = __DIR__ . '/../Fixtures/media.ged';
        $parser = new GedcomParser();
        $parser->parse(DB::connection(), $filename, 'test-slug', false);

        $media = DB::table('media_objects')->get();
        $this->assertCount(2, $media);
        $this->assertEquals('Photo of John Doe', $media->first()->title);
    }
}
