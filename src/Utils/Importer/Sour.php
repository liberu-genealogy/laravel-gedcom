<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer;

use FamilyTree365\LaravelGedcom\Models\Source;

class Sour
{
    /**
     * Gedcom\Record\Subn $subn
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, $sour, $obje_ids = [])
    {
        if ($sour == null || is_array($sour)) {
            return 0;
        }
        $auth = mb_convert_encoding($sour->getAuth(), 'UTF-8'); // string
        $titl = mb_convert_encoding($sour->getTitl(), 'UTF-8'); // string
        $abbr = mb_convert_encoding($sour->getAbbr(), 'UTF-8'); // string
        $publ = mb_convert_encoding($sour->getPubl(), 'UTF-8'); // string
        $rin = mb_convert_encoding($sour->getRin(), 'UTF-8'); // string
        $text = mb_convert_encoding($sour->getText(), 'UTF-8'); // string

        $record = app(Source::class)->on($conn)->updateOrCreate(
            ['titl' => $titl, 'rin' => $rin, 'auth' => $auth, 'text' => $text, 'publ' => $publ, 'abbr' => $abbr],
            ['titl' => $titl, 'rin' => $rin, 'auth' => $auth, 'text' => $text, 'publ' => $publ, 'abbr' => $abbr]
        );

        $_group = 'sour';
        $_gid = $record->id;

        $chan = $sour->getChan(); // Record/Chan
        if ($chan !== null) {
            \FamilyTree365\LaravelGedcom\Utils\Importer\Chan::read($conn, $chan, $_group, $_gid = 0);
        }
        $repo = $sour->getRepo(); // Repo
        if ($repo !== null) {
            \FamilyTree365\LaravelGedcom\Utils\Importer\Sour\Repo::read($conn, $repo, $_group, $_gid = 0);
        }
        $data = $sour->getData(); // object
        if ($data !== null) {
            \FamilyTree365\LaravelGedcom\Utils\Importer\Sour\Data::read($conn, $data, $_group, $_gid = 0);
        }
        $refn = $sour->getRefn(); // array
        foreach ($refn as $item) {
            if ($item) {
                \FamilyTree365\LaravelGedcom\Utils\Importer\Refn::read($conn, $item, $_group, $_gid = 0);
            }
        }

        $note = $sour->getNote(); // array
        if ($note != null && (is_countable($note) ? count($note) : 0) > 0) {
            foreach ($note as $item) {
                \FamilyTree365\LaravelGedcom\Utils\Importer\NoteRef::read($conn, $item, $_group, $_gid);
            }
        }

        $obje = $sour->getObje(); // array
        foreach ($obje as $item) {
            if ($item) {
                \FamilyTree365\LaravelGedcom\Utils\Importer\ObjeRef::read($conn, $item, $_group, $_gid, $obje_ids);
            }
        }

        $sour->getSour(); // string id

        return $_gid;
    }
}
