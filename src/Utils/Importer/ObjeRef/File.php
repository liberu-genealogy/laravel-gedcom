<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\ObjeRef;

use App\MediaObjeectFile;

class File
{
    /**
     * PhpGedcom\Record\Sour\Data\Even $even
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \PhpGedcom\Record\ObjeRef\File $file, $group = '', $group_id = 0)
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
