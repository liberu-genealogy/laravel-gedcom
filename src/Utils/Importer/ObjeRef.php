<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\MediaObject;
use \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef;

class ObjeRef
{
    /**
     * \PhpGedcom\Record\ObjeRef $objeref
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($conn,\PhpGedcom\Record\ObjeRef $objeref, $group='', $group_id=0)
    {
        if($objeref == null) {
            return;
        }
        $titl = $objeref->getTitl();
        $file = $objeref->getFile();
        $form = $objeref->getForm();

        // store MediaObject
        $key = ['group'=>$group,'gid'=>$group_id, 'titl'=>$titl, 'file'=>$file, 'form'=>$form];
        $data = [
            'group'=>$group,
            'gid'=>$group_id,
            'titl'=>$titl,
            'file'=>$file,
            'form'=>$form,
        ];
        $record = MediaObject::on($conn)->updateOrCreate($key, $data);

        $_group = 'objeref';
        $_gid = $record->id;
        // store Note
        $notes = $objeref->getNote();
        if($notes && count($notes) > 0) { 
            foreach($notes as $item) { 
                NoteRef::read($conn,$item, $_group, $_gid);
            }
        }
        return;
    }
}
