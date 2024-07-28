<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Commands\GedcomExporter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Mockery;

class GedcomExporterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function testHandlingOfFileWriteErrors()
    {
        Storage::shouldReceive('put')
            ->once()
            ->andThrow(new \Exception('Failed to write file'));

        $this->artisan('gedcom:export', ['filename' => 'test_export'])
            ->expectsOutput('An error occurred while exporting the GEDCOM file: Failed to write file')
            ->assertExitCode(1);
    }

    public static function exportDataProvider()
    {
        return [
            'individuals' => [
                ['type' => 'individuals', 'data' => ['name' => 'John Doe']],
                "0 @I1@ INDI\n1 NAME John Doe\n"
            ],
            'families' => [
                ['type' => 'families', 'data' => ['id' => 'F1']],
                "0 @F1@ FAM\n"
            ],
            'notes' => [
                ['type' => 'notes', 'data' => ['content' => 'Note for individual']],
                "0 @N1@ NOTE Note for individual\n"
            ],
            'media' => [
                ['type' => 'media_objects', 'data' => ['title' => 'Photo of John Doe']],
                "0 @M1@ OBJE\n1 TITL Photo of John Doe\n"
            ],
        ];
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}