<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\Sour;
use \App\SourceData;
use \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef;
use \ModularSoftware\LaravelGedcom\Utils\Importer\Sour\Data\Even;

class Data
{
    /**
     * PhpGedcom\Record\Sour\Data $data
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read(\PhpGedcom\Record\Sour\Data $data, $group='', $group_id=0)
    {
        $date = $data->getDate();
        $agnc = $data->getAgnc();
        $text = $data->getData();

        // store Data of sources
        $key = ['group'=>$group,'gid'=>$group_id, 'date'=>$date, 'text'=>$text, 'agnc'=>$agnc];
        $data = ['group'=>$group,'gid'=>$group_id, 'date'=>$date, 'text'=>$text, 'agnc'=>$agnc];
        $record = SourceData::updateOrCreate($key, $data);

        $_group = 'sourcedata';
        $_gid = $record->id;
        // \PhpGedcom\Record\Sour\Data\Even array
        $even = $data->getEven();
        if($even && count($even) > 0) {
            foreach($even as $item) {
                Even::read($item, $_group, $_gid);
            }
        }
        // \PhpGedcom\Record\NoteRef array
        $note = $data->getNote();
        if($note && count($note) > 0) {
            foreach($note as $item) {
                NoteRef::read($item, $_group, $_gid);
            }
        }
        return;
    }
}
