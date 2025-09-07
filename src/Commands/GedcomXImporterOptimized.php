<?php

declare(strict_types=1);

namespace FamilyTree365\LaravelGedcom\Commands;

use FamilyTree365\LaravelGedcom\Utils\GedcomXParserOptimized;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * High-performance GedcomX importer using PHP 8.4 optimizations
 */
final class GedcomXImporterOptimized extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gedcomx:import-optimized 
                            {file : The path to the GedcomX file to import}
                            {--connection= : Database connection to use}
                            {--slug= : Unique identifier for this import}
                            {--tenant= : Tenant ID for multi-tenant applications}
                            {--progress : Show progress bar during import}
                            {--memory-limit=512 : Memory limit in MB}
                            {--chunk-size=1000 : Batch processing chunk size}';

    /**
     * The console command description.
     */
    protected $description = 'Import a GedcomX file with optimized performance (PHP 8.4+)';

    /**
     * Execute the console command with performance optimizations
     */
    public function handle(): int
    {
        $filename = $this->argument('file');
        $connection = $this->option('connection') ?? config('database.default');
        $slug = $this->option('slug') ?? 'gedcomx-optimized-' . time();
        $tenant = $this->option('tenant');
        $showProgress = $this->option('progress');
        $memoryLimit = (int)$this->option('memory-limit');
        $chunkSize = (int)$this->option('chunk-size');

        // PHP 8.4: Set memory limit dynamically
        ini_set('memory_limit', $memoryLimit . 'M');

        // Validate file exists and is readable
        if (!is_file($filename) || !is_readable($filename)) {
            $this->error("File not found or not readable: {$filename}");
            return 1;
        }

        // Validate it's a GedcomX file using optimized validation
        if (!GedcomXParserOptimized::isGedcomXFile($filename)) {
            $this->error("File does not appear to be a valid GedcomX file: {$filename}");
            return 1;
        }

        $this->info("🚀 Starting optimized GedcomX import...");
        $this->info("📁 File: {$filename}");
        $this->info("🔗 Connection: {$connection}");
        $this->info("🏷️  Slug: {$slug}");
        $this->info("💾 Memory Limit: {$memoryLimit}MB");
        $this->info("📦 Chunk Size: {$chunkSize}");

        if ($tenant) {
            $this->info("🏢 Tenant: {$tenant}");
        }

        try {
            $startTime = microtime(true);
            $startMemory = memory_get_usage(true) / 1_048_576;

            $this->info("📊 Initial Memory Usage: " . round($startMemory, 2) . "MB");

            $parser = new GedcomXParserOptimized();
            $channel = [
                'name' => 'gedcomx-optimized-progress',
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
            $endMemory = memory_get_usage(true) / 1_048_576;
            $peakMemory = memory_get_peak_usage(true) / 1_048_576;
            $executionTime = round($endTime - $startTime, 2);

            $this->info("✅ GedcomX import completed successfully!");
            $this->info("⏱️  Execution time: {$executionTime} seconds");
            $this->info("📊 Final Memory Usage: " . round($endMemory, 2) . "MB");
            $this->info("📈 Peak Memory Usage: " . round($peakMemory, 2) . "MB");
            $this->info("💡 Memory Efficiency: " . round(($startMemory / $peakMemory) * 100, 1) . "%");

            // Performance metrics
            $fileSize = filesize($filename) / 1_048_576; // MB
            $throughput = round($fileSize / $executionTime, 2);
            $this->info("🚄 Throughput: {$throughput} MB/s");

            return 0;

        } catch (\Throwable $e) {
            $this->error("❌ GedcomX import failed: " . $e->getMessage());

            Log::error("Optimized GedcomX import error", [
                'file' => $filename,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true)
            ]);

            return 1;
        }
    }
}