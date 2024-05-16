<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Models\Subm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class GedcomExporter
{
    public static function exportData($filename)
    {
        $dir = 'public/gedcom/exported';

        $filename = $filename . '.GED';

        if (!file_exists($dir)) {
            Storage::makeDirectory($dir);
        }

        $query = app(Subm::class)->query();
        $query->join('addrs', 'addrs.id', '=', 'subms.addr_id');
        $query->select([
            'subms.name',
            'addrs.adr1',
            'addrs.adr2',
            'addrs.city',
            'addrs.stae',
            'addrs.post',
            'addrs.ctry',
            'subms.phon',
        ]);

        $people = app(Person::class)->all();
        $submissions = $query->get();

        $data = [
            'submissions' => $submissions,
            'people' => $people,
        ];

        $source = View::make('stubs.ged', $data)->render();

        $ged_doc = "HEAD \nGEDC \nVERS 5.5.5 \nFORM LINEAGE-LINKED \nVERS 5.5.5 \nCHAR UTF-8 \nSOUR GS \nVERS 5.5.5 \nCORP gedcom.org\n";

        $handle = fopen($filename, 'w');

        fwrite($handle, $ged_doc . $source);

        fclose($handle);
    }
}
