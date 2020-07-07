<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\Source;
use \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef;
use \ModularSoftware\LaravelGedcom\Utils\Importer\Sour\Data;
use \ModularSoftware\LaravelGedcom\Utils\Importer\SourRef\Even;

class SourRef
{
    /**
     * PhpGedcom\Record\SourRef $sourref
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($conn, \PhpGedcom\Record\SourRef $sourref, $group='', $group_id=0)
    {
        if($sourref == null) {
            return;
        }
        $sour = $sourref->getSour();
        $text = $sourref->getText();
        $quay = $sourref->getQuay();
        $page = $sourref->getPage();

        // store Source
        $key = ['group'=>$group,'gid'=>$group_id, 'note'=>$sour];
        $data = [
            'group'=>$group,
            'gid'=>$group_id,
            'sour'=>$sour,
            'text'=>$text,
            'quay'=>$quay,
            'page'=>$page,
        ];
        $record = Source::on($conn)->updateOrCreate($key, $data);

        $_group = 'sourref';
        $_gid = $record->id;
        // store Note
        $notes = $sourref->getNote();
        if($notes && count($notes) > 0) { 
            foreach($notes as $item) { 
                NoteRef::read($conn, $item, $_group, $_gid);
            }
        }

        // store Data
        $data = $sourref->getData();
        if($data) {
            Data::read($conn, $data,  $_group, $_gid);
        }

        // store \PhpGedcom\Record\SourRef\Even
        $even = $sourref->getEven();
        if($even) {
            Even::read($conn, $even, $_group, $_gid);
        }

        return;
    }
}
