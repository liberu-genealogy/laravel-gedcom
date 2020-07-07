<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\Subm as MSubm;
class Subm
{
    /**
     * PhpGedcom\Record\Subm $noteref
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($conn,$subm, $group=null, $gid= null)
    {
        if($subm == null || is_array($subm)) {
            return ;
        }

        $name = $subm->getName() ?? null; // string
        if(!is_object($subm)) {
            $name = $subm;
        }
        $addr = $subm->getAddr() ?? null;
        $addr_id = \ModularSoftware\LaravelGedcom\Utils\Importer\Addr::read($conn,$addr);
        $_phon = $subm->getPhon() ?? null; // array
        $phon = \ModularSoftware\LaravelGedcom\Utils\Importer\Phon::read($conn,$_phon);
        $rin  = $subm->getRin() ?? null; // string
        $rfn  = $subm->getRfn() ?? null; // string 

        $_lang = $subm->getLang(); // string array
        $lang = json_encode($_lang);
        $key = [
            'group' => $group,
            'gid' => $gid,
            'name' => $name,
            'addr_id' => $addr_id,
            'phon' => $phon,
            'rin'=>$rin,
            'rfn' => $rfn,
        ];
        $data = [
            'group' => $group,
            'gid' => $gid,
            'name' => $name,
            'addr_id' => $addr_id,
            'phon' => $phon,
            'rin'=>$rin,
            'rfn' => $rfn,
            'lang' => $lang,
        ];
        $record = MSubm::on($conn)->updateOrCreate($key, $data);
        $_group = 'subm';
        $_gid = $record->id;

        $note = $subm->getNote();  // array ---

        if($note != null && count($note) > 0) {
            foreach($note as $item) {
                \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef::read($conn,$item, $_group, $_gid);
            }
        }
        $obje = $subm->getObje() ?? null;  // array ---
        if($obje && count($obje) > 0) {
            foreach($obje as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\ObjeRef::read($conn,$item, $_group, $_gid);
                }
            }
        }
        $chan = $subm->getChan() ?? null; // Record\Chan---
        if($chan !== null) {
            \ModularSoftware\LaravelGedcom\Utils\Importer\Chan::read($conn,$chan, $_group, $_gid);
        }
        return ;
    }
}