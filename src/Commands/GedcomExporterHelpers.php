<?php

namespace FamilyTree365\LaravelGedcom\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class GedcomExporterHelpers
{
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
