<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils\Importer;

use App\MediaObject;

class Obje
{
    /**
     * PhpGedcom\Record\Obje $obje
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \PhpGedcom\Record\Obje $obje, $group = '', $group_id = 0)
    {
        if ($obje == null) {
            return 0;
        }

        $id = $obje->getId();
        $rin = $obje->getRin(); // string

        // store Object
        $key = [
            'group'   => $group,
            'gid'     => $group_id,
            'rin'     => $rin,
            'obje_id' => $id,
        ];
        $data = [
            'group'   => $group,
            'gid'     => $group_id,
            'rin'     => $rin,
            'obje_id' => $id,
        ];

        $record = MediaObject::on($conn)->updateOrCreate($key, $data);

        $_group = 'obje';
        $_gid = $record->id;

        $refn = $obje->getRefn(); // Record/Refn array
        if ($refn && count($refn) > 0) {
            foreach ($refn as $item) {
                Refn::read($conn, $item, $_group, $_gid);
            }
        }

        // store Note
        $note = $obje->getNote(); // Record/NoteRef array
        if ($note && count($note) > 0) {
            foreach ($note as $item) {
                NoteRef::read($conn, $item, $_group, $_gid);
            }
        }

        // store Note
        $files = $obje->getFile(); // Record/NoteRef array
        if (($files && count((is_countable($files) ? $files : [])))) {
            foreach ($files as $item) {
                \GenealogiaWebsite\LaravelGedcom\Utils\Importer\ObjeRef\File::read($conn, $item, $_group, $_gid);
            }
        }

        $chan = $obje->getChan(); // Recore/Chan
        if ($chan !== null) {
            \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Chan::read($conn, $chan, $_group, $_gid);
        }

        return $_gid;
    }
}
