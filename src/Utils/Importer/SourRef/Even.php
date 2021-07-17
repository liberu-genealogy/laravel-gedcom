<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer\SourRef;

use FamilyTree365\LaravelGedcom\Models\SourceRefEven;

class Even
{
    /**
     * Gedcom\Record\Sour\Data\Even $even
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \Gedcom\Record\SourRef\Even $even, $group = '', $group_id = 0)
    {
        $_even = $even->getEven();
        $role = $even->getRole();

        // store Even of source/data
        $key = ['group'=>$group, 'gid'=>$group_id, 'even'=>$_even, 'role'=>$role];
        $data = ['group'=>$group, 'gid'=>$group_id, 'even'=>$_even, 'role'=>$role];
        $record = SourceRefEven::on($conn)->updateOrCreate($key, $data);
    }
}
