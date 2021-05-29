<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer\Fam;

use FamilyTree365\LaravelGedcom\Models\FamilySlgs;

class Slgs
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID.
     *
     * @var string
     */
    public static function read($conn, $slgs, $fam)
    {
        if ($slgs == null || $fam === null) {
            return;
        }

        $stat = $slgs->getStat();
        $date = $slgs->getDate();
        $plac = $slgs->getPlac();
        $temp = $slgs->getTemp();

        $key = [
            'family_id'=> $fam->id,
            'stat'     => $stat,
            'date'     => $date,
            'plac'     => $plac,
            'temp'     => $temp,
        ];
        $data = [
            'family_id'=> $fam->id,
            'stat'     => $stat,
            'date'     => $date,
            'plac'     => $plac,
            'temp'     => $temp,
        ];

        $record = FamilySlgs::on($conn)->updateOrCreate($key, $data);

        $_group = 'fam_slgs';
        $_gid = $record->id;

        // array
        $sour = $slgs->getSour();
        if ($sour && count($sour) > 0) {
            foreach ($sour as $item) {
                if ($item) {
                    \FamilyTree365\LaravelGedcom\Utils\Importer\SourRef::read($conn, $item, $_group, $_gid);
                }
            }
        }

        $note = $slgs->getNote();
        if ($note && count($note) > 0) {
            foreach ($note as $item) {
                \FamilyTree365\LaravelGedcom\Utils\Importer\NoteRef::read($conn, $item, $_group, $_gid);
            }
        }
    }
}
