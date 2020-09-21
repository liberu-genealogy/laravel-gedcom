<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils\Importer;

use App\Subn as MSubn;

class Subn
{
    /**
     * PhpGedcom\Record\Subn $subn
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, $subn, $subm_ids)
    {
        if ($subn == null || is_array($subn)) {
            return;
        }
        $_subm = $subn->getSubm();
        $subm = null;
        if (isset($subm_ids[$subm])) {
            $subm = $subm_ids[$_subm];
        }
        $famf = $subn->getFamf();
        $temp = $subn->getTemp();
        $ance = $subn->getAnce();
        $desc = $subn->getDesc();
        $ordi = $subn->getOrdi();
        $rin = $subn->getRin();
        $record = MSubn::on($conn)->updateOrCreate(compact('subm', 'famf', 'temp', 'ance', 'desc', 'ordi', 'rin'), compact('subm', 'famf', 'temp', 'ance', 'desc', 'ordi', 'rin'));

        $_group = 'subn';
        $_gid = $record->id;

        $note = $subn->getNote();  // array ---

        if ($note != null && count($note) > 0) {
            foreach ($note as $item) {
                \GenealogiaWebsite\LaravelGedcom\Utils\Importer\NoteRef::read($conn, $item, $_group, $_gid);
            }
        }
        $chan = $subn->getChan() ?? null; // Record\Chan---
        if ($chan !== null) {
            \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Chan::read($conn, $chan, $_group, $_gid);
        }
    }
}
