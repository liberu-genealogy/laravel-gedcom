<?php

namespace FamilyTree365\LaravelGedcom\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

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

    public static function fetchDatabaseData()
    {
        $query = DB::table('subms')->join('addrs', 'addrs.id', '=', 'subms.addr_id')->select([
            'subms.name',
            'addrs.adr1',
            'addrs.adr2',
            'addrs.city',
            'addrs.stae',
            'addrs.post',
            'addrs.ctry',
            'subms.phon',
        ]);

        return $query->get();
    }

    public static function prepareDataForView($submissions, $people)
    /**
     * Fetches data from the database by joining 'subms' and 'addrs' tables.
     *
     * @return \Illuminate\Support\Collection The fetched data.
     */
    {
        return [
            'submissions' => $submissions,
            'people'      => $people,
        ];
    }

    public static function createGedcomDocumentString($source)
    {
        $gedcomHeader = "HEAD \nGEDC \nVERS 5.5.5 \nFORM LINEAGE-LINKED \nVERS 5.5.5 \nCHAR UTF-8 \nSOUR GS \nVERS 5.5.5 \nCORP gedcom.org\n";
        return $gedcomHeader . $source;
    }

    public static function writeToFile($filename, $content)
    {
        $handle = fopen($filename, 'w');
        fwrite($handle, $content);
        fclose($handle);
    }
}
/**
 * This file contains helper functions for exporting GEDCOM data from the database.
 * It includes methods for directory creation, data fetching, data preparation for views,
 * GEDCOM document string creation, and writing content to files.
 */
    /**
     * Creates a directory if it does not already exist.
     *
     * @param string $dir The directory to create.
     */
    /**
     * Creates a GEDCOM document string with a header and the provided source content.
     *
     * @param string $source The GEDCOM content to append to the header.
     * @return string The complete GEDCOM document string.
     */
    /**
     * Writes content to a file.
     *
     * @param string $filename The name of the file to write to.
     * @param string $content The content to write to the file.
     */
    /**
     * Prepares data for view by organizing submissions and people into an array.
     *
     * @param \Illuminate\Support\Collection $submissions The submissions data.
     * @param \Illuminate\Support\Collection $people The people data.
     * @return array The prepared data for view.
     */
