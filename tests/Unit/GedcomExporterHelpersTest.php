<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Commands\GedcomExporterHelpers;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Mockery;

class GedcomExporterHelpersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        DB::shouldReceive('table')
            ->andReturnSelf()
            ->shouldReceive('join')
            ->andReturnSelf()
            ->shouldReceive('select')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->andReturn(collect([]));
    }

    protected function getPackageProviders($app)
    {
        return ['FamilyTree365\LaravelGedcom\ServiceProvider'];
    }

    public function testCreateDirectory()
    {
        GedcomExporterHelpers::createDirectory('test-dir');
        Storage::disk('local')->assertExists('test-dir');
    }

    public function testFetchDatabaseData()
    {
        $data = GedcomExporterHelpers::fetchDatabaseData();
        $this->assertCount(0, $data);

        // No records found
        DB::shouldReceive('get')->andReturn(collect([]));
        $data = GedcomExporterHelpers::fetchDatabaseData();
        $this->assertCount(0, $data);
    }

    public function testPrepareDataForView()
    {
        $submissions = ['submission1', 'submission2'];
        $people = ['person1', 'person2'];

        $result = GedcomExporterHelpers::prepareDataForView($submissions, $people);
        $this->assertEquals(['submissions' => $submissions, 'people' => $people], $result);
    }

    public function testCreateGedcomDocumentString()
    {
        $source = "0 @I1@ INDI\n1 NAME John Doe";
        $expectedResult = "HEAD \nGEDC \nVERS 5.5.5 \nFORM LINEAGE-LINKED \nVERS 5.5.5 \nCHAR UTF-8 \nSOUR GS \nVERS 5.5.5 \nCORP gedcom.org\n" . $source;
        $result = GedcomExporterHelpers::createGedcomDocumentString($source);
        $this->assertEquals($expectedResult, $result);
    }

    public function testWriteToFile()
    {
        $filename = 'testFile.txt';
        $content = 'Test content';

        GedcomExporterHelpers::writeToFile(storage_path('app/public/' . $filename), $content);
        Storage::disk('local')->assertExists('public/' . $filename);
        $this->assertEquals($content, Storage::disk('local')->get('public/' . $filename));

        // Simulate file write error
        Storage::shouldReceive('put')->andThrow(new \Exception('Failed to write to file'));
        $this->expectException(\Exception::class);
        GedcomExporterHelpers::writeToFile(storage_path('app/public/errorFile.txt'), 'Error content');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}