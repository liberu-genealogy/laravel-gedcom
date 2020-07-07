<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\Source;
class Sour
{
    /**
     * PhpGedcom\Record\Subn $subn
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read($conn,$sour)
    {
        if($sour == null || is_array($sour)) {
            return ;
        }
        $titl = $sour->getTitl(); // string
        $rin = $sour->getRin(); // string
        $auth = $sour->getAuth(); // string
        $text = $sour->getText(); // string
        $publ = $sour->getPubl(); // string
        $abbr = $sour->getAbbr(); // string


        $record = Source::on($conn)->updateOrCreate(compact('titl','rin', 'auth', 'text', 'publ', 'abbr'), 
        compact('titl', 'rin', 'auth', 'text', 'publ', 'abbr') );

        $_group = 'sour';
        $_gid = $record->id;

        $chan = $sour->getChan(); // Record/Chan
        if($chan !== null)  {
            \ModularSoftware\LaravelGedcom\Utils\Importer\Chan::read($conn,$chan, $_group, $_gid=0);
        }
        $repo = $sour->getRepo(); // Repo
        if($repo !== null) {
            \ModularSoftware\LaravelGedcom\Utils\Importer\RepoRef::read($conn,$repo, $_group, $_gid=0);
        }
        $data = $sour->getData(); // object
        if($data !== null) {
            \ModularSoftware\LaravelGedcom\Utils\Importer\Sour\Data::read($conn,$data, $_group, $_gid=0);
        }
        $refn = $sour->getRefn(); // array
        if($refn && count($refn) > 0) {
            foreach($refn as $item) {
                if($item) { 
                    \ModularSoftware\LaravelGedcom\Utils\Importer\Refn::read($conn,$item, $_group, $_gid=0);
                }
            }
        }

        $note = $sour->getNote(); // array
        if($note != null && count($note) > 0) {
            foreach($note as $item) {
                \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef::read($conn,$item, $_group, $_gid);
            }
        }
        
        $obje = $sour->getObje(); // array
        if($obje && count($obje) > 0) {
            foreach($obje as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\ObjeRef::read($conn,$item, $_group, $_gid);
                }
            }
        }

        $sour = $sour->getSour(); // string id


        return ;
    }
}
