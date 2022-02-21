<?php

namespace FamilyTree365\LaravelGedcom\Utils\Exporter\Sour\Data;

use FamilyTree365\LaravelGedcom\Models\SourceDataEven;

class Even
{
    /**
     * Gedcom\Record\Sour\Data\Even $even
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \Gedcom\Record\Sour\Data\Even $even, $group = '', $group_id = 0)
    {
        $date = $even->getDate();
        $plac = $even->getPlac();

        // store Even of source/data
        $key = ['group'=>$group, 'gid'=>$group_id, 'date'=>$date, 'plac'=>$plac];
        $data = ['group'=>$group, 'gid'=>$group_id, 'date'=>$date, 'plac'=>$plac];
        $record = SourceDataEven::on($conn)->updateOrCreate($key, $data);
    }
}
