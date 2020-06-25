<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\Addr as MAddr;
class Caln
{
    /**
     * PhpGedcom\Record\Caln $caln
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($caln, $group=null, $gid=null)
    {
        if($caln == null || is_array($caln)) {
            return;
        }
        
        $medi = $addr->getMedi();

        $key = [
            'group'=>$group,
            'gid'=>$gid,
            'medi'=>$medi,
        ];
        $data = [
            'group'=>$group,
            'gid'=>$gid,
            'medi'=>$medi,
        ];
        $_caln = MCaln::updateOrCreate($key, $data);
        
        return;
    }
}