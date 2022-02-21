<?php

namespace FamilyTree365\LaravelGedcom\Utils\Exporter\Indi\Name;

use FamilyTree365\LaravelGedcom\Models\PersonNameRomn;

class Romn
{
    /**
     * \Gedcom\Record\Indi\Asso $asso
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \Gedcom\Record\Indi\Name\Romn $item, $group = '', $group_id = 0)
    {
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
            'npfx' => $npfx,
            'givn' => $givn,
            'nick' => $nick,
            'spfx' => $spfx,
            'surn' => $surn,
            'nsfx' => $nsfx,
        ];

        $record = PersonNameRomn::on($conn)->updateOrCreate($key, $data);
    }
}
