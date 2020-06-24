<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\Indi;
use \App\PersonSubm;

class Subm
{
    /**
     * String $subm
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read(string $subm, $group='', $group_id=0)
    {
        // store alia 
        $key = ['group'=>$group,'gid'=>$group_id, 'subm'=>$subm];
        $data = ['group'=>$group,'gid'=>$group_id, 'subm'=>$subm];
        $record = PersonSubm::updateOrCreate($key, $data);
    }
}
