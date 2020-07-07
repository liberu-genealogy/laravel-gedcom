<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\Repository;
use \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef;
use \ModularSoftware\LaravelGedcom\Utils\Importer\Refn;
use \ModularSoftware\LaravelGedcom\Utils\Importer\Caln;

class Repo
{
    /**
     * PhpGedcom\Record\Repo $repo
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($conn,\PhpGedcom\Record\Repo $repo, $group='', $group_id=0)
    {
        if($repo == null) {
            return;
        }
        $name = $repo->getName(); // string
        $rin = $repo->getRin(); // string
        $addr = $repo->getAddr(); // Record/Addr
        $addr_id = \ModularSoftware\LaravelGedcom\Utils\Importer\Addr::read($conn,$addr);
        $_phon = $repo->getPhon(); // Record/Phon array
        $phon = \ModularSoftware\LaravelGedcom\Utils\Importer\Phon::read($conn,$_phon);
        
        // store Source
        $key = [
            'group'=>$group,
            'gid'=>$group_id, 
            'name' => $name,
            'rin' => $rin,
            'addr_id' => $addr_id,
            'phon' => $phon,
        ];
        $data = [
            'group'=>$group,
            'gid'=>$group_id, 
            'name' => $name,
            'rin' => $rin,
            'addr_id' => $addr_id,
            'phon' => $phon,
        ];

        $record = Repository::on($conn)->updateOrCreate($key, $data);

        $_group = 'repo';
        $_gid = $record->id;
        // store Note
        $note = $repo->getNote(); // Record/NoteRef array
        if($note && count($note) > 0) { 
            foreach($note as $item) { 
                NoteRef::read($conn,$item, $_group, $_gid);
            }
        }
        $refn = $repo->getRefn(); // Record/Refn array
        if($refn && count($refn) > 0) { 
            foreach($refn as $item) { 
                Refn::read($conn,$item, $_group, $_gid);
            }
        }

        $chan = $repo->getChan(); // Recore/Chan 
        if($chan !== null) {
            \ModularSoftware\LaravelGedcom\Utils\Importer\Chan::read($conn,$chan, $_group, $_gid);
        }
        return;
    }
}
