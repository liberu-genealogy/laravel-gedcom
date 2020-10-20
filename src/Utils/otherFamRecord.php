<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils;

use GenealogiaWebsite\LaravelGedcom\Models\Family;
use GenealogiaWebsite\LaravelGedcom\Models\Person;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Chan;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Fam\Even;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Fam\Slgs;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\NoteRef;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\ObjeRef;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Refn;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\SourRef;
use GenealogiaWebsite\LaravelGedcom\Utils\Importer\Subm;

class otherFamRecord
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID.
     *
     * @var string
     */
    protected $persons_id = [];
    protected $subm_ids = [];
    protected $sour_ids = [];
    protected $obje_ids = [];
    protected $note_ids = [];
    protected $repo_ids = [];
    protected $conn = '';

    public static function insertFamilyData($conn, $families, $obje_ids)
    {
        foreach ($families as $family) {
            $g_id = $family->getId();
            $husb = $family->getHusb();
            $wife = $family->getWife();
            $husband_id = (isset($persons_id[$husb])) ? $persons_id[$husb] : 0;
            $wife_id = (isset($persons_id[$wife])) ? $persons_id[$wife] : 0;
            $children = $family->getChil();
            $events = $family->getAllEven();
            $subm = $family->getSubm();
            $_slgs = $family->getSlgs();
            $_note = $family->getNote();
            $_obje = $family->getObje();
            $_sour = $family->getSour();
            $_refn = $family->getRefn();
            $chan = $family->getChan();

            $familie = Family::on($conn)->where('husband_id',$husband_id)->where('wife_id',$wife_id)->first();
          
            if ($children !== null) {
                foreach ($children as $child) {
                    if (isset($persons_id[$child])) {
                        $person = Person::on($conn)->find($persons_id[$child]);
                        $person->child_in_family_id = $familie->id;
                        $person->save();
                    }
                }
            }

            if ($events !== null && count($events) > 0) {
                foreach ($events as $item) {
                    if ($item) {
                        Even::read($conn, $item, $familie, $obje_ids);
                    }
                    // $date = $getDate($item->getDate());
                    // $place = $getPlace($item->getPlac());
                    // $family->addEvent($item->getType(), $date, $place);
                }
            }
            $_group = 'fam';
            $_gid = $familie->id;
            if ($_note != null && count($_note) > 0) {
                foreach ($_note as $item) {
                    NoteRef::read($conn, $item, $_group, $_gid);
                }
            }
            if ($_obje && count($_obje) > 0) {
                foreach ($_obje as $item) {
                    if ($item) {
                        ObjeRef::read($conn, $item, $_group, $_gid, $obje_ids);
                    }
                }
            }
            if ($_refn && count($_refn) > 0) {
                foreach ($_refn as $item) {
                    if ($item) {
                        Refn::read($conn, $item, $_group, $_gid);
                    }
                }
            }
            if ($_sour && count($_sour) > 0) {
                foreach ($_sour as $item) {
                    if ($item) {
                        SourRef::read($conn, $item, $_group, $_gid, $sour_ids, $obje_ids);
                    }
                }
            }
            if ($_slgs && count($_slgs) > 0) {
                foreach ($_slgs as $item) {
                    if ($item) {
                        Slgs::read($conn, $item, $familie);
                    }
                }
            }
            if ($subm && count($subm) > 0) {
                foreach ($subm as $item) {
                    if ($item) {
                        Subm::read($conn, $item, $_group, $_gid, $obje_ids);
                    }
                }
            }
            if ($chan) {
                Chan::read($conn, $chan, 'family', $familie->id);
            }
        }
    }
}
