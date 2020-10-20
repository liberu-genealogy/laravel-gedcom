<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi;

use GenealogiaWebsite\LaravelGedcom\Models\PersonAlia;

class Alia
{
    /**
     * String $alia
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, $item, $group = '', $group_id = 0)
    {
        $aliaData = [];
        foreach ($item as $alia) {
            if ($alia) {
                $data = ['group'=>$group, 'gid'=>$group_id, 'alia'=>$alia];
                $aliaData[] = $data;
            }
        }
        PersonAlia::on($conn)->insert($aliaData);
    }
}
