<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils\Importer;

use GenealogiaWebsite\LaravelGedcom\Models\SourceRef;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Sour\Data;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\SourRef\Even;

class SourRef
{
    /**
     * PhpGedcom\Record\SourRef $sourref
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \PhpGedcom\Record\SourRef $sourref, $group = '', $group_id = 0, $sour_ids = [], $obje_ids = [])
    {
        if ($sourref == null) {
            return;
        }

        $sour = $sourref->getSour();
        if (!isset($sour_ids[$sour])) {
            return;
        }
        $sour_id = $sour_ids[$sour];
        $text = $sourref->getText();
        $quay = $sourref->getQuay();
        $page = $sourref->getPage();

        // store Source
        $key = ['group'=>$group, 'gid'=>$group_id, 'sour_id'=>$sour_id];
        $data = [
            'group'  => $group,
            'gid'    => $group_id,
            'sour_id'=> $sour_id,
            'text'   => $text,
            'quay'   => $quay,
            'page'   => $page,
        ];
        $record = SourceRef::on($conn)->updateOrCreate($key, $data);

        $_group = 'sourref';
        $_gid = $record->id;
        // store MediaObje
        $objes = $sourref->getObje();
        if ($objes && count($objes) > 0) {
            foreach ($objes as $item) {
                ObjeRef::read($conn, $item, $_group, $_gid, $obje_ids);
            }
        }

        // store Note
        $notes = $sourref->getNote();
        if ($notes && count($notes) > 0) {
            foreach ($notes as $item) {
                NoteRef::read($conn, $item, $_group, $_gid);
            }
        }

        // store Data
        $data = $sourref->getData();
        if ($data) {
            Data::read($conn, $data, $_group, $_gid);
        }

        // store \PhpGedcom\Record\SourRef\Even
        $even = $sourref->getEven();
        if ($even) {
            Even::read($conn, $even, $_group, $_gid);
        }
    }
}
