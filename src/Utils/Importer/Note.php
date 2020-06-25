<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\Note as MNote; 
use \ModularSoftware\LaravelGedcom\Utils\Importer\SourRef;
use \ModularSoftware\LaravelGedcom\Utils\Importer\Chan;
use \ModularSoftware\LaravelGedcom\Utils\Importer\Refn;

class Note
{
    /**
     * PhpGedcom\Record\NoteRef $noteref
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read(\PhpGedcom\Record\Note $note, $group='', $group_id=0)
    {
        $_note = $note->getNote();
        $rin = $note->getRin();

        // store note 
        $key = ['group'=>$group, 'gid'=>$group_id, 'note'=>$_note];
        $data = ['group'=>$group, 'gid'=>$group_id, 'note'=>$_note, 'rin'=>$rin];
        $record = MNote::updateOrCreate($key, $data);

        // store Sources of Note
        $_group = 'note';
        $_gid = $record->id;
        // SourRef array
        $sour = $note->getSour();
        if($sour && count($sour) > 0){
            foreach($sour as $item) {
                SourRef::read($item, $_group, $_gid);
            }
        }
        // Refn array
        $refn = $note->getRefn();
        if($refn && count($refn) > 0){
            foreach($refn as $item) {
                Refn::read($item, $_group, $_gid);
            }
        }

        // Chan 
        $chan = $note->getChan();
        if($chan !== null){
            Chan::read($chan, $_group, $_gid);
        }
        return;
    }
}
