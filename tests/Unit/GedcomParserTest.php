<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Utils\GedcomParser;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;
use Mockery;

class GedcomParserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock DB facade
        DB::shouldReceive('connection')
            ->andReturn(Mockery::mock('Illuminate\Database\Connection'));

        DB::shouldReceive('disableQueryLog')
            ->andReturn(true);
    }

    protected function getPackageProviders($app)
    {
        return ['FamilyTree365\LaravelGedcom\ServiceProvider'];
    }

    public function testParseWithValidFile()
    {
        $parser = new GedcomParser();
        $result = $parser->parse('mysql', __DIR__ . '/../Fixtures/sample.ged', 'test-slug', false);
        $this->assertTrue($result);
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

    public function testParseWithProgressReporting()
    {
        $filename = __DIR__ . '/../Fixtures/complete.ged';
        $channel = ['name' => 'test-channel', 'eventName' => 'testEvent'];
        $parser = new GedcomParser();
        $parser->parse(DB::connection(), $filename, 'test-slug', true, $channel);

        Event::assertDispatched(GedComProgressSent::class, function ($event) use ($channel) {
            return $event->channel === $channel && $event->currentProgress === 10;
        });
    }

    public function testParseWithExceptionHandling()
    {
        $filename = __DIR__ . '/../Fixtures/invalid.ged';
        $parser = new GedcomParser();
        Log::shouldReceive('error')->once();

        $this->expectException(\Exception::class);
        $parser->parse(DB::connection(), $filename, 'test-slug', false);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}