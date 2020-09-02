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

    public static function read($conn,string $anci, $group='', $group_id=0, $subm_ids)
    {
        // store alia 
        if(isset($subm_ids[$anci])) {
            $subm_id = $subm_ids[$anci];
            $key = ['group'=>$group,'gid'=>$group_id, 'anci'=>$subm_id];
            $data = ['group'=>$group,'gid'=>$group_id, 'anci'=>$subm_id];
            $record = PersonAnci::on($conn)->updateOrCreate($key, $data);
        }
        return;
    }
}
