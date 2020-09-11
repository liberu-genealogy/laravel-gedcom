<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\Indi;

use App\PersonSubm;

class Subm
{
    /**
     * String $subm
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, string $subm, $group = '', $group_id = 0, $subm_ids)
    {
        // store alia
        if (isset($subm_ids[$subm])) {
            $subm_id = $subm_ids[$subm];
            $key = ['group'=>$group, 'gid'=>$group_id, 'subm'=>$subm_id];
            $data = ['group'=>$group, 'gid'=>$group_id, 'subm'=>$subm_id];
            $record = PersonSubm::on($conn)->updateOrCreate($key, $data);
        }
    }
}
