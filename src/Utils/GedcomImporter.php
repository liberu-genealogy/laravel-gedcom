<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use FamilyTree365\LaravelGedcom\Facades\GedcomParserFacade;
use FamilyTree365\LaravelGedcom\Models\Person;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class GedcomImporter
{
    public static function importData($filename)
    {
        $gedFile = $filename . '.GED';
        $slug = $filename;

        // Code extracted from GedcomImporter.php handle() function (lines 41-42)
        GedcomParserFacade::parse('mysql', $gedFile, $slug, true);
    }
}
