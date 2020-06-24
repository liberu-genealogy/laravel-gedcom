<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\SourRef;
use \App\SourceRefEven;
class Even
{
    /**
     * PhpGedcom\Record\Sour\Data\Even $even
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read(\PhpGedcom\Record\SourRef\Even $even, $group='', $group_id=0)
    {
        $_even = $even->getEven();
        $role = $even->getRole();

        // store Even of source/data
        $key = ['group'=>$group,'gid'=>$group_id, 'even'=>$_even, 'role'=>$role];
        $data = ['group'=>$group,'gid'=>$group_id, 'even'=>$_even, 'role'=>$role];
        $record = SourceRefEven::updateOrCreate($key, $data);

        return;
    }
}
