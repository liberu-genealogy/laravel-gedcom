<?php

namespace Tests\Unit;

use Tests\TestCase;
use FamilyTree365\LaravelGedcom\Facades\GedcomParserFacade;
use FamilyTree365\LaravelGedcom\Jobs\GedcomImportJob;
use Illuminate\Support\Facades\Queue;

class GedcomImportJobTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return ['FamilyTree365\LaravelGedcom\ServiceProvider'];
    }

    public function testJobIsDispatchedByGedcomImporter(): void
    {
        Queue::fake();

        \FamilyTree365\LaravelGedcom\Utils\GedcomImporter::importData('family');

        Queue::assertPushed(GedcomImportJob::class, function ($job) {
            return $job->conn === 'mysql'
                && $job->filename === 'family.ged'
                && $job->slug === 'family';
        });
    }

    public function testGedcomImporterDispatchesWithLowercaseGedExtension(): void
    {
        Queue::fake();

        \FamilyTree365\LaravelGedcom\Utils\GedcomImporter::importData('family');

        Queue::assertPushed(GedcomImportJob::class, function ($job) {
            return str_ends_with($job->filename, '.ged')
                && !str_ends_with($job->filename, '.GED');
        });
    }

    public function testJobHandleCallsParser(): void
    {
        GedcomParserFacade::shouldReceive('parse')
            ->once()
            ->with('mysql', 'family.ged', 'family', true)
            ->andReturn(null);

        $job = new GedcomImportJob('mysql', 'family.ged', 'family');
        $job->handle();
    }

    public function testJobImplementsShouldQueue(): void
    {
        $job = new GedcomImportJob('mysql', 'test.ged', 'test');
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $job);
    }
}
