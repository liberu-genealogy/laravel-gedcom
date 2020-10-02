<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils\Importer\Sour;

use GenealogiaWebsite\LaravelGedcom\Models\SourceData;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\NoteRef;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Sour\Data\Even;

class Data
{
    /**
     * PhpGedcom\Record\Sour\Data $data
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \PhpGedcom\Record\Sour\Data $data, $group = '', $group_id = 0)
    {
        $date = $data->getDate();
        $agnc = $data->getAgnc();
        $text = $data->getText();

        // store Data of sources
        $key = ['group'=>$group, 'gid'=>$group_id, 'date'=>$date, 'text'=>$text, 'agnc'=>$agnc];
        $_data = ['group'=>$group, 'gid'=>$group_id, 'date'=>$date, 'text'=>$text, 'agnc'=>$agnc];
        $record = SourceData::on($conn)->updateOrCreate($key, $_data);

        $_group = 'sourcedata';
        $_gid = $record->id;
        // \PhpGedcom\Record\Sour\Data\Even array
        $even = $data->getEven();
        if ($even && count($even) > 0) {
            foreach ($even as $item) {
                Even::read($conn, $item, $_group, $_gid);
            }
        }
        // \PhpGedcom\Record\NoteRef array
        $note = $data->getNote();
        if ($note && count($note) > 0) {
            foreach ($note as $item) {
                NoteRef::read($conn, $item, $_group, $_gid);
            }
        }
    }
}
