<?php

namespace FamilyTree365\LaravelGedcom\Commands;

use FamilyTree365\LaravelGedcom\Utils\GedcomXParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * GedcomXImporter command for importing GedcomX files via Artisan
 */
class GedcomXImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gedcomx:import 
                            {file : The path to the GedcomX file to import}
                            {--connection= : Database connection to use}
                            {--slug= : Unique identifier for this import}
                            {--tenant= : Tenant ID for multi-tenant applications}
                            {--progress : Show progress bar during import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a GedcomX file into the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filename = $this->argument('file');
        $connection = $this->option('connection') ?? config('database.default');
        $slug = $this->option('slug') ?? 'gedcomx-import-' . time();
        $tenant = $this->option('tenant');
        $showProgress = $this->option('progress');

        // Validate file exists
        if (!file_exists($filename)) {
            $this->error("File not found: {$filename}");
            return 1;
        }

        // Validate it's a GedcomX file
        if (!GedcomXParser::isGedcomXFile($filename)) {
            $this->error("File does not appear to be a valid GedcomX file: {$filename}");
            return 1;
        }

        $this->info("Starting GedcomX import...");
        $this->info("File: {$filename}");
        $this->info("Connection: {$connection}");
        $this->info("Slug: {$slug}");

        if ($tenant) {
            $this->info("Tenant: {$tenant}");
        }

        try {
            $startTime = microtime(true);

            $parser = new GedcomXParser();
            $channel = [
                'name' => 'gedcomx-console-progress',
                'eventName' => 'importProgress'
            ];

            $parser->parse(
                $connection,
                $filename,
                $slug,
                $showProgress,
                $tenant,
                $channel
            );

            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);

            $this->info("GedcomX import completed successfully!");
            $this->info("Execution time: {$executionTime} seconds");

            return 0;

        } catch (\Exception $e) {
            $this->error("GedcomX import failed: " . $e->getMessage());
            Log::error("GedcomX import error", [
                'file' => $filename,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}