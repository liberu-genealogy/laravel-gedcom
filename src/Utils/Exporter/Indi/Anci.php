<?php

namespace FamilyTree365\LaravelGedcom\Utils\Exporter\Indi;

use FamilyTree365\LaravelGedcom\Models\PersonAnci;

class Anci
{
    /**
     * String $anci
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, $item, $subm_ids, $group = '', $group_id = 0)
    {
        $record = [];
        foreach ($item as $anci) {
            // store alia
            if ($anci && isset($subm_ids[$anci])) {
                $subm_id = $subm_ids[$anci];
                $key = ['group'=>$group, 'gid'=>$group_id, 'anci'=>$subm_id];
                $data = ['group'=>$group, 'gid'=>$group_id, 'anci'=>$subm_id];
                $record[] = $data;
            }
        }
        PersonAnci::insert($record);
    }
}
