<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Commands\GedcomExporter;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Mockery;

class GedcomExporterHelpersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function testCreateDirectory()
    {
        GedcomExporter::createDirectory('test-dir');
        Storage::disk('local')->assertExists('test-dir');
    }

    public function testFetchDatabaseData()
    {
        DB::shouldReceive('table')
            ->with('submissions')
            ->andReturnSelf()
            ->shouldReceive('join')
            ->andReturnSelf()
            ->shouldReceive('select')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->andReturn(collect([]));

        $data = GedcomExporter::fetchDatabaseData();
        $this->assertCount(0, $data);
    }

    public function testPrepareDataForView()
    {
        $submissions = ['submission1', 'submission2'];
        $people = ['person1', 'person2'];

        $result = GedcomExporter::prepareDataForView($submissions, $people);
        $this->assertEquals(['submissions' => $submissions, 'people' => $people], $result);
    }

    public function testCreateGedcomDocumentString()
    {
        $source = "0 @I1@ INDI\n1 NAME John Doe";
        $expectedResult = "HEAD \nGEDC \nVERS 5.5.5 \nFORM LINEAGE-LINKED \nVERS 5.5.5 \nCHAR UTF-8 \nSOUR GS \nVERS 5.5.5 \nCORP gedcom.org\n" . $source;
        $result = GedcomExporter::createGedcomDocumentString($source);
        $this->assertEquals($expectedResult, $result);
    }

    public function testWriteToFile()
    {
        $filename = sys_get_temp_dir() . '/gedcom_test_' . uniqid() . '.txt';
        $content = 'Test content';

        GedcomExporter::writeToFile($filename, $content);

        $this->assertFileExists($filename);
        $this->assertEquals($content, file_get_contents($filename));

        unlink($filename);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}