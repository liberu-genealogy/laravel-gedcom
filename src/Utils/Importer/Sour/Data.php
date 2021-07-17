<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer\Sour;

use FamilyTree365\LaravelGedcom\Models\SourceData;
use FamilyTree365\LaravelGedcom\Utils\Importer\NoteRef;
use FamilyTree365\LaravelGedcom\Utils\Importer\Sour\Data\Even;

class Data
{
    /**
     * Gedcom\Record\Sour\Data $data
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \Gedcom\Record\Sour\Data $data, $group = '', $group_id = 0)
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
        // \Gedcom\Record\Sour\Data\Even array
        $even = $data->getEven();
        if ($even && count($even) > 0) {
            foreach ($even as $item) {
                Even::read($conn, $item, $_group, $_gid);
            }
        }
        // \Gedcom\Record\NoteRef array
        $note = $data->getNote();
        if ($note && count($note) > 0) {
            foreach ($note as $item) {
                NoteRef::read($conn, $item, $_group, $_gid);
            }
        }
    }
}
