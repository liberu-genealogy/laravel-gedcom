<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\Indi;
use \App\PersonAnci;

class Anci
{
    /**
     * String $anci
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($conn,string $anci, $group='', $group_id=0)
    {
        // store alia 
        $key = ['group'=>$group,'gid'=>$group_id, 'anci'=>$anci];
        $data = ['group'=>$group,'gid'=>$group_id, 'anci'=>$anci];
        $record = PersonAnci::on($conn)->updateOrCreate($key, $data);
    }
}
