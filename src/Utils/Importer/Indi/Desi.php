<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\Indi;
use \App\PersonDesi;

class Desi
{
    /**
     * String $desi
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read(string $desi, $group='', $group_id=0)
    {
        // store alia 
        $key = ['group'=>$group,'gid'=>$group_id, 'desi'=>$desi];
        $data = ['group'=>$group,'gid'=>$group_id, 'desi'=>$desi];
        $record = PersonDesi::updateOrCreate($key, $data);
    }
}
