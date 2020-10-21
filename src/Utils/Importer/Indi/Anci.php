<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi;

use GenealogiaWebsite\LaravelGedcom\Models\PersonAnci;

class Anci
{
    /**
     * String $anci
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, $item, $group = '', $group_id = 0, $subm_ids)
    {
        $record = [];
        foreach ($item as $anci) {
            if ($anci) {
                // store alia
                if (isset($subm_ids[$anci])) {
                    $subm_id = $subm_ids[$anci];
                    $key = ['group'=>$group, 'gid'=>$group_id, 'anci'=>$subm_id];
                    $data = ['group'=>$group, 'gid'=>$group_id, 'anci'=>$subm_id];
                    $record [] = $data;
                }
            }
        }
        PersonAnci::insert($record);
    }
}
