<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Commands\GedcomExporterHelpers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class GedcomExporterHelpersTest extends TestCase
{
    public function testCreateDirectory()
    {
        Storage::fake('local');
        GedcomExporterHelpers::createDirectory('testDir');
        Storage::disk('local')->assertExists('testDir');

        // Directory already exists
        GedcomExporterHelpers::createDirectory('testDir');
        Storage::disk('local')->assertExists('testDir');
    }

    public function testFetchDatabaseData()
    {
        DB::shouldReceive('table')
            ->with('subms')
            ->andReturnSelf()
            ->shouldReceive('join')
            ->with('addrs', 'addrs.id', '=', 'subms.addr_id')
            ->andReturnSelf()
            ->shouldReceive('select')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->andReturn(collect([
                (object)['name' => 'John Doe', 'adr1' => '123 Main St', 'city' => 'Anytown', 'stae' => 'CA', 'post' => '12345', 'ctry' => 'USA', 'phon' => '555-1234'],
            ]));

        $data = GedcomExporterHelpers::fetchDatabaseData();
        $this->assertCount(1, $data);

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
        Storage::fake('local');
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
}

