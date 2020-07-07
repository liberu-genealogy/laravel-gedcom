<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\Note;
use \ModularSoftware\LaravelGedcom\Utils\Importer\SourRef;
class NoteRef
{
    /**
     * PhpGedcom\Record\NoteRef $noteref
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($conn, \PhpGedcom\Record\NoteRef $noteref, $group='', $group_id=0)
    {
        $note = $noteref->getNote();

        // store note 
        $key = ['group'=>$group,'gid'=>$group_id, 'note'=>$note];
        $data = ['group'=>$group,'gid'=>$group_id, 'note'=>$note];
        $record = Note::on($conn)->updateOrCreate($key, $data);

        // store Sources of Note
        $_group = 'note';
        $_gid = $record->id;
        // SourRef array
        $sour = $noteref->getSour();
        if($sour && count($sour) > 0){
            foreach($sour as $item) {
                SourRef::read($conn, $item, $_group, $_gid);
            }
        }
        return;
    }
}
