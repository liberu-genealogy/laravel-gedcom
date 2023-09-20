<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer;

use FamilyTree365\LaravelGedcom\Models\Note as MNote;
use Throwable;

class Note
{
    /**
     * Gedcom\Record\NoteRef $noteref
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \Gedcom\Record\Note $note, $group = '', $group_id = 0)
    {
        try {
            $_note = $note->getNote();
            $rin = $note->getRin();

            // store note
            $key = ['group'=>$group, 'gid'=>$group_id, 'note'=> mb_convert_encoding((string) $_note, 'UTF-8', 'ISO-8859-1')];
            $data = ['group'=>$group, 'gid'=>$group_id, 'note'=> mb_convert_encoding((string) $_note, 'UTF-8', 'ISO-8859-1'), 'rin'=>$rin];
            $record = MNote::on($conn)->updateOrCreate($key, $data);

            // store Sources of Note
            $_group = 'note';
            $_gid = $record->id;
            // SourRef array
            $sour = $note->getSour();
            foreach ($sour as $item) {
                SourRef::read($conn, $item, $_group, $_gid);
            }
            // Refn array
            $refn = $note->getRefn();
            foreach ($refn as $item) {
                Refn::read($conn, $item, $_group, $_gid);
            }

            // Chan
            $chan = $note->getChan();
            if ($chan !== null) {
                Chan::read($conn, $chan, $_group, $_gid);
            }

            return $_gid;
        } catch (Throwable $e) {
            report($e);
        }
    }
}
