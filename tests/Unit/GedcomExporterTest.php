<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
        DB::shouldReceive('table')
            ->with('submissions')
            ->andThrow(new \Exception('Database connection failed'));

        $this->artisan('gedcom:export', ['filename' => 'test_export'])
            ->expectsOutput('An error occurred while exporting the GEDCOM file: Database connection failed')
            ->assertExitCode(1);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}