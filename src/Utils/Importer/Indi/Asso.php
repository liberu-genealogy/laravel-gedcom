<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\Indi;

use App\PersonAsso;

class Asso
{
    /**
     * \PhpGedcom\Record\Indi\Asso $asso
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \PhpGedcom\Record\Indi\Asso $asso, $group = '', $group_id = 0)
    {
        $indi = $group_id;
        $_indi = $asso->getIndi();
        $rela = $asso->getRela();

        // store asso
        $key = ['group'=>$group, 'gid'=>$group_id, 'rela'=>$rela, 'indi' => $_indi];
        $data = ['group'=>$group, 'gid'=>$group_id, 'rela'=>$rela, 'indi' => $_indi];
        $record = PersonAsso::on($conn)->updateOrCreate($key, $data);

        $_group = 'indi_asso';
        $_gid = $record->id;
        // store Note
        $note = $asso->getNote();
        if ($note && count($note) > 0) {
            foreach ($note as $item) {
                if ($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef::read($conn, $item, $_group, $_gid);
                }
            }
        }

        // store sourref
        $sour = $asso->getSour();
        if ($sour && count($sour) > 0) {
            foreach ($sour as $item) {
                if ($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\SourRef::read($conn, $item, $_group, $_gid);
                }
            }
        }
    }
}
