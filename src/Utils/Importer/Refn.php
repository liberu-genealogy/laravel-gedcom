<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\Refn as MRefn;
class Refn
{
    /**
     * PhpGedcom\Record\Refn $noteref
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read(\PhpGedcom\Record\Refn $refn, $group='', $group_id=0)
    {
        $_refn = $refn->getRefn();
        $type = $refn->getType();
        // store refn
        $key = ['group'=>$group,'gid'=>$group_id, 'refn'=>$_refn, 'type'=>$type];
        $data = ['group'=>$group,'gid'=>$group_id, 'refn'=>$_refn, 'type'=>$type];
        $record = MRefn::updateOrCreate($key, $data);

        return;
    }
}
