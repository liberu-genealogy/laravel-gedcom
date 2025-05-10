<?php

namespace FamilyTree365\LaravelGedcom\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait GedcomExporterHelpers
{
    /**
     * Creates a directory if it doesn't exist.
     *
     * @param string $dir Directory path
     * @return void
     */
    public static function createDirectory($dir)
    {
        if (!Storage::exists($dir)) {
            Storage::makeDirectory($dir);
        }
    }

    /**
     * Fetches data from the database for GEDCOM export.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function fetchDatabaseData()
    {
        return DB::table('submissions')
            ->join('submitters', 'submissions.subm_id', '=', 'submitters.id')
            ->select('submissions.*', 'submitters.name as submitter_name')
            ->get();
    }

    /**
     * Creates a GEDCOM document string with proper headers.
     *
     * @param string $source The GEDCOM content
     * @return string The formatted GEDCOM document
     */
    public static function createGedcomDocumentString($source)
    {
        return "HEAD \nGEDC \nVERS 5.5.5 \nFORM LINEAGE-LINKED \nVERS 5.5.5 \nCHAR UTF-8 \nSOUR GS \nVERS 5.5.5 \nCORP gedcom.org\n" . $source;
    }

    /**
     * Writes content to a file.
     *
     * @param string $fullPath Path to write the file
     * @param string $content Content to write
     * @return void
     * @throws \Exception If file writing fails
     */
    public static function writeToFile($fullPath, $content)
    {
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        if (file_put_contents($fullPath, $content) === false) {
            throw new \Exception("Failed to write GEDCOM file to: {$fullPath}");
        }
    }
}