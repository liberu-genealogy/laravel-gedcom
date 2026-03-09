<?php

namespace Tests\Unit;

use Tests\TestCase;
use FamilyTree365\LaravelGedcom\Facades\GedcomParserFacade;

class GedcomImporterTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return ['FamilyTree365\LaravelGedcom\ServiceProvider'];
    }

    public function testImportWithValidFile(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'gedcom_test_') . '.ged';
        file_put_contents($tmpFile, '0 HEAD\n1 GEDC\n2 VERS 5.5.1\n0 TRLR');

        GedcomParserFacade::shouldReceive('parse')
            ->once()
            ->with('mysql', $tmpFile, pathinfo($tmpFile, PATHINFO_FILENAME), true)
            ->andReturn(null);

        $this->artisan('gedcom:import', ['filename' => $tmpFile])
            ->assertExitCode(0);

        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }
    }

    public function testImportWithInvalidFileExtension(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'gedcom_test_') . '.txt';
        file_put_contents($tmpFile, 'Not a gedcom file');

        GedcomParserFacade::shouldReceive('parse')
            ->never();

        $this->artisan('gedcom:import', ['filename' => $tmpFile])
            ->assertExitCode(1);

        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }
    }
}
