<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils\Importer\Sour\Data;

use GenealogiaWebsite\LaravelGedcom\Models\SourceDataEven;

class Even
{
    /**
     * PhpGedcom\Record\Sour\Data\Even $even
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \PhpGedcom\Record\Sour\Data\Even $even, $group = '', $group_id = 0)
    {
        $date = $even->getDate();
        $plac = $even->getPlac();

        // store Even of source/data
        $key = ['group'=>$group, 'gid'=>$group_id, 'date'=>$date, 'plac'=>$plac];
        $data = ['group'=>$group, 'gid'=>$group_id, 'date'=>$date, 'plac'=>$plac];
        $record = SourceDataEven::on($conn)->updateOrCreate($key, $data);
    }
}
