<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer\Indi;

use FamilyTree365\LaravelGedcom\Models\PersonDesi;

class Desi
{
    /**
     * String $desi
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, string $desi, $group = '', $group_id = 0, $subm_ids)
    {
        // store alia
        if (isset($subm_ids[$desi])) {
            $subm_id = $subm_ids[$desi];
            $key = ['group'=>$group, 'gid'=>$group_id, 'desi'=>$desi];
            $data = ['group'=>$group, 'gid'=>$group_id, 'desi'=>$desi];
            $record = PersonDesi::on($conn)->updateOrCreate($key, $data);
        }
    }
}
