<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\MediaObject;
use \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef;
use \ModularSoftware\LaravelGedcom\Utils\Importer\Refn;
use \ModularSoftware\LaravelGedcom\Utils\Importer\Caln;

class Obje
{
    /**
     * PhpGedcom\Record\Obje $obje
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($conn,\PhpGedcom\Record\Obje $obje, $group='', $group_id=0)
    {
        if($obje == null) {
            return 0;
        }

        $form = $obje->getForm(); // string
        $titl = $obje->getTitl(); // string
        $blob = $obje->getBlob(); // string
        $rin = $obje->getRin(); // string
        $file = $obje->getFile(); // string


        // store Object
        $key = [
            'group'=>$group,
            'gid'=>$group_id, 
            'form' => $form,
            'rin' => $rin,
            'titl' => $titl,
            'blob' => $blob,
            'file' => $file,
        ];
        $data = [
            'group'=>$group,
            'gid'=>$group_id, 
            'form' => $form,
            'rin' => $rin,
            'titl' => $titl,
            'blob' => $blob,
            'file' => $file,
        ];

        $record = MediaObject::on($conn)->updateOrCreate($key, $data);

        $_group = 'obje';
        $_gid = $record->id;

        $refn = $obje->getRefn(); // Record/Refn array
        if($refn && count($refn) > 0) { 
            foreach($refn as $item) { 
                Refn::read($conn,$item, $_group, $_gid);
            }
        }

        // store Note
        $note = $obje->getNote(); // Record/NoteRef array
        if($note && count($note) > 0) { 
            foreach($note as $item) { 
                NoteRef::read($conn,$item, $_group, $_gid);
            }
        }

        $chan = $obje->getChan(); // Recore/Chan 
        if($chan !== null) {
            \ModularSoftware\LaravelGedcom\Utils\Importer\Chan::read($conn,$chan, $_group, $_gid);
        }
        return $_gid;
    }
}
