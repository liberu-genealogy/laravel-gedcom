<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer\ObjeRef;

use FamilyTree365\LaravelGedcom\Models\MediaObjeectFile;

class File
{
    /**
     * Gedcom\Record\Sour\Data\Even $even
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \Gedcom\Record\ObjeRef\File $file, $group = '', $group_id = 0)
    {
        $form = $file->getForm();
        $medi = null;
        $type = null;
        if ($form) {
            $medi = $form->getMedi();
        }

        // store File
        $key = ['group'=>$group, 'gid'=>$group_id, 'form'=>$form, 'medi'=>$medi, 'type'=> $type];
        $data = ['group'=>$group, 'gid'=>$group_id, 'form'=>$form, 'medi'=>$medi, 'type'=>$type];
        $record = MediaObjeectFile::on($conn)->updateOrCreate($key, $data);
    }
}
