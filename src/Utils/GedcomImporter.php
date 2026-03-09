<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use FamilyTree365\LaravelGedcom\Jobs\GedcomImportJob;

class GedcomImporter
{
    public static function importData(string $filename): void
    {
        $gedFile = $filename . '.ged';
        $slug = $filename;

        GedcomImportJob::dispatch('mysql', $gedFile, $slug);
    }
}
