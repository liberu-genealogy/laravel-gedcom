<?php

namespace FamilyTree365\LaravelGedcom\Utils\Exporter\Indi;

use FamilyTree365\LaravelGedcom\Models\PersonSubm;

class Subm
{
    /**
     * String $subm
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, $item, $subm_ids, $group = '', $group_id = 0)
    {
        $record = [];
        foreach ($item as $subm) {
            // store alia
            if ($subm && isset($subm_ids[$subm])) {
                $subm_id = $subm_ids[$subm];
                $key = ['group'=>$group, 'gid'=>$group_id, 'subm'=>$subm_id];
                $data = ['group'=>$group, 'gid'=>$group_id, 'subm'=>$subm_id];
                $record[] = $data;
            }
        }

        PersonSubm::on($conn)->insert($record);
    }
}
