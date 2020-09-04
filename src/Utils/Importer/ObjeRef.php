<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\MediaObject;
use \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef;
use \ModularSoftware\LaravelGedcom\Utils\Importer\ObjeRef\File;
class ObjeRef
{
    /**
     * \PhpGedcom\Record\ObjeRef $objeref
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($conn,\PhpGedcom\Record\ObjeRef $objeref, $group='', $group_id=0, $obje_ids = [])
    {
        if($objeref == null) {
            return ;
        }
        $obje_id = $objeref->getObje();
        if(isset($obje_ids[$obje_id])) {
            $obje_id = $obje_ids[$obje_id];
        }
        $titl = $objeref->getTitl();
        $file = $objeref->getFile();

        // store MediaObject
        $key = ['group'=>$group,'gid'=>$group_id, 'titl'=>$titl];
        $data = [
            'group'=>$group,
            'gid'=>$group_id,
            'obje_id'=>$obje_id,
            'titl'=>$titl,
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
        $files = $objeref->getFile();
        if($files && count($files) > 0) {
            foreach($files as $item) {
                File::read($conn, $item, $_group, $_gid);
            }
        }
        return;
    }
}
