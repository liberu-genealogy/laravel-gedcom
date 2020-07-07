<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\Indi;
use \App\PersonAlia;

class Alia
{
    /**
     * String $alia
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($conn,string $alia, $group='', $group_id=0)
    {
        // store alia 
        $key = ['group'=>$group,'gid'=>$group_id, 'alia'=>$alia];
        $data = ['group'=>$group,'gid'=>$group_id, 'alia'=>$alia];
        $record = PersonAlia::on($conn)->updateOrCreate($key, $data);
    }
}
