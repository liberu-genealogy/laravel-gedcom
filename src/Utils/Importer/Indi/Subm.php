<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi;

use GenealogiaWebsite\LaravelGedcom\Models\PersonSubm;

class Subm
{
    /**
     * String $subm
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, $item, $group = '', $group_id = 0, $subm_ids)
    {
        $record = [];
        foreach ($item as $subm) {
            if ($subm) {
                // store alia
                if (isset($subm_ids[$subm])) {
                    $subm_id = $subm_ids[$subm];
                    $key = ['group'=>$group, 'gid'=>$group_id, 'subm'=>$subm_id];
                    $data = ['group'=>$group, 'gid'=>$group_id, 'subm'=>$subm_id];
                    $record[] = $data;
                }
            }
        }

        PersonSubm::insert($record);
    }
}
