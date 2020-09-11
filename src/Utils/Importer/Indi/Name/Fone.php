<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\Indi\Name;

use App\PersonNameFone;

class Fone
{
    /**
     * \PhpGedcom\Record\Indi\Asso $asso
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \PhpGedcom\Record\Indi\Name\Fone $item, $group = '', $group_id = 0)
    {
        $type = $item->getType();
        $name = $item->getName();
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

        $record = PersonNameFone::on($conn)->updateOrCreate($key, $data);
    }
}
