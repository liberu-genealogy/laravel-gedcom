<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer\Indi;

use FamilyTree365\LaravelGedcom\Models\PersonName;

class Name
{
    /**
     * \PhpGedcom\Record\Indi\Asso $asso
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \PhpGedcom\Record\Indi\Name $item, $group = '', $group_id = 0)
    {
        $name = $item->getName();
        $type = $item->getType();
        $npfx = $item->getNpfx();
        $givn = $item->getGivn();
        $nick = $item->getNick();
        $spfx = $item->getSpfx();
        $surn = $item->getSurn();
        $nsfx = $item->getNsfx();

        // store asso
        $key = [
            'group'=> $group,
            'gid'  => $group_id,
            'type' => $type,
            'name' => $name,
            'npfx' => $npfx,
            'givn' => $givn,
            'nick' => $nick,
            'spfx' => $spfx,
            'surn' => $surn,
            'nsfx' => $nsfx,
        ];
        $data = [
            'group'=> $group,
            'gid'  => $group_id,
            'type' => $type,
            'name' => $name,
            'npfx' => $npfx,
            'givn' => $givn,
            'nick' => $nick,
            'spfx' => $spfx,
            'surn' => $surn,
            'nsfx' => $nsfx,
        ];

        $record = PersonName::on($conn)->updateOrCreate($key, $data);

        $_group = 'indi_name';
        $_gid = $record->id;
        // store Note
        $note = $item->getNote();
        if ($note && count($note) > 0) {
            foreach ($note as $_item) {
                if ($_item) {
                    \FamilyTree365\LaravelGedcom\Utils\Importer\NoteRef::read($conn, $_item, $_group, $_gid);
                }
            }
        }

        // store sourref
        $sour = $item->getSour();
        if ($sour && count($sour) > 0) {
            foreach ($sour as $_item) {
                if ($_item) {
                    \FamilyTree365\LaravelGedcom\Utils\Importer\SourRef::read($conn, $_item, $_group, $_gid);
                }
            }
        }

        // store fone
        $fone = $item->getFone();
        if ($fone != null) {
            \FamilyTree365\LaravelGedcom\Utils\Importer\Indi\Name\Fone::read($conn, $fone, $_group, $_gid);
        }

        // store romn
        $romn = $item->getRomn();
        if ($romn != null) {
            \FamilyTree365\LaravelGedcom\Utils\Importer\Indi\Name\Romn::read($conn, $romn, $_group, $_gid);
        }
    }
}
