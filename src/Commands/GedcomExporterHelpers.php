<?php

namespace FamilyTree365\LaravelGedcom\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class GedcomExporterHelpers
{
    /**
     * Creates a directory if it does not already exist.
     *
     * @param string $dir The directory path to create.
     */
    public static function createDirectory($dir)
    {
        if (!Storage::exists($dir)) {
            Storage::makeDirectory($dir);
        }
    }

    /**
     * Fetches submission and address data from the database.
     *
     * @return \Illuminate\Support\Collection Collection of database records.
     */
    public static function fetchDatabaseData()
    {
        $query = DB::table('subms')
            ->leftJoin('addrs', 'addrs.id', '=', 'subms.addr_id')
            ->select([
                'subms.id',
                'subms.name',
                'subms.gid',
                'subms.email',
                'subms.phon',
                'subms.www',
                'addrs.adr1',
                'addrs.adr2',
                'addrs.city',
                'addrs.stae',
                'addrs.post',
                'addrs.ctry',
            ]);

        return $query->get();
    }

    /**
     * Prepares submission and people data for view rendering.
     *
     * @param \Illuminate\Support\Collection $submissions Collection of submissions.
     * @param \Illuminate\Support\Collection $people Collection of people data.
     * @return array Array containing prepared data for view.
     */
    public static function prepareDataForView($submissions, $people)
    {
        return [
            'submissions' => $submissions,
            'people'      => $people,
        ];
    }

    /**
     * Creates a GEDCOM document string with a header and the provided source content.
     *
     * @param string $source The GEDCOM content to be included after the header.
     * @return string The complete GEDCOM document string.
     */
    public static function createGedcomDocumentString($source)
    {
        $now = Carbon::now();
        $date = strtoupper($now->format('d M Y'));
        $time = $now->format('H:i:s');

        $gedcomHeader = "0 HEAD\n" .
            "1 GEDC\n" .
            "2 VERS 5.5.1\n" .
            "2 FORM LINEAGE-LINKED\n" .
            "1 CHAR UTF-8\n" .
            "1 SOUR GS\n" .
            "2 NAME " . env('APP_NAME', 'Family Tree 365') . "\n" .
            "2 VERS 1.0\n" .
            "2 CORP gedcom.org\n" .
            "1 DATE $date\n" .
            "2 TIME $time\n" .
            "1 FILE " . basename($source) . "\n" .
            "1 LANG English\n";

        return $gedcomHeader . $source;
    }

    /**
     * Writes the provided content to a file.
     *
     * @param string $filename The name of the file to write to.
     * @param string $content The content to write to the file.
     * @throws \Exception If file writing fails.
     */
    public static function writeToFile($filename, $content)
    {
        $directory = dirname($filename);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $handle = fopen($filename, 'w');
        if (!$handle) {
            throw new \Exception("Could not open file for writing: $filename");
        }

        if (fwrite($handle, $content) === false) {
            fclose($handle);
            throw new \Exception("Failed to write to file: $filename");
        }

        fclose($handle);
    }
}