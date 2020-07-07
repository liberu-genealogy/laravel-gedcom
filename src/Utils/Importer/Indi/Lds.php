<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\Indi;
use \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef;
use \ModularSoftware\LaravelGedcom\Utils\Importer\SourRef;
use \App\PersonLds;
class Lds
{
    /**
     * PhpGedcom\Record\Indi\Lds $lds
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($conn,\PhpGedcom\Record\Indi\Lds $lds, $group='', $group_id=0, $type='')
    {
        $stat = $lds->getStat();
        $date = $lds->getDate();
        $plac = $lds->getPlac();
        $temp = $lds->getTemp();

        $slgc_famc = '';
        if($type == 'SLGC') {
            $slgc_famc = $lds->getFamc();
        }
        // store refn
        $key = [
            'group'=>$group,
            'gid'=>$group_id, 
            'type'=>$type, 
            'stat'=>$stat,
            'date'=>$date,
            'plac'=>$plac,
            'temp'=>$temp,
            'slgc_famc' =>$slgc_famc,
        ];
        $data = [
            'group'=>$group,
            'gid'=>$group_id, 
            'type'=>$type, 
            'stat'=>$stat,
            'date'=>$date,
            'plac'=>$plac,
            'temp'=>$temp,
            'slgc_famc' =>$slgc_famc,
        ];
        $record = PersonLds::on($conn)->updateOrCreate($key, $data);

        $_group = 'indi_lds';
        $_gid = $record->id;
        // add sour
        $sour = $lds->getSour();
        if($sour && count($sour) > 0) {
            foreach($sour as $item) {
                if($item) {
                    SourRef::read($conn, $item, $_group, $_gid);
                }
            }
        }

        // add note
        $note = $lds->getNote();
        if($note && count($note) > 0) {
            foreach($note as $item) {
                if($item) {
                    NoteRef::read($conn,$item, $_group, $_gid);
                }
            }
        }
        return;
    }
}
