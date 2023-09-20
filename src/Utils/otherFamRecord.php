<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Utils\Importer\Chan;
use FamilyTree365\LaravelGedcom\Utils\Importer\Fam\Even;
use FamilyTree365\LaravelGedcom\Utils\Importer\Fam\Slgs;
use FamilyTree365\LaravelGedcom\Utils\Importer\NoteRef;
use FamilyTree365\LaravelGedcom\Utils\Importer\ObjeRef;
use FamilyTree365\LaravelGedcom\Utils\Importer\Refn;
use FamilyTree365\LaravelGedcom\Utils\Importer\SourRef;
use FamilyTree365\LaravelGedcom\Utils\Importer\Subm;

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

    public static function insertFamilyData($conn, $persons_id, $families, $obje_ids, $sour_ids, $note_ids = [], $repo_ids = [])
    {
        foreach ($families as $family) {
            $g_id = $family->getId();
            $husb = $family->getHusb();
            $wife = $family->getWife();
            $husband_id = $persons_id[$husb] ?? 0;
            $wife_id = $persons_id[$wife] ?? 0;
            $children = $family->getChil();
            $events = $family->getAllEven();
            $subm = $family->getSubm();
            $_slgs = $family->getSlgs();
            $_note = $family->getNote();
            $_obje = $family->getObje();
            $_sour = $family->getSour();
            $_refn = $family->getRefn();
            $chan = $family->getChan();
            $familie = Family::on($conn)->where('husband_id', $husband_id)->where('wife_id', $wife_id)->first();
            if ($children !== null) {
                foreach ($children as $child) {
                    if (isset($persons_id[$child])) {
                        $person = Person::on($conn)->find($persons_id[$child]);
                        $person->child_in_family_id = $familie->id;
                        $person->save();
                    }
                }
            }

            if ($events !== null && (is_countable($events) ? count($events) : 0) > 0) {
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
            $_gid = $familie == null ? 0 : $familie->id;
            if ($_note != null && (is_countable($_note) ? count($_note) : 0) > 0) {
                foreach ($_note as $item) {
                    NoteRef::read($conn, $item, $_group, $_gid);
                }
            }
            foreach ($_obje as $item) {
                if ($item) {
                    ObjeRef::read($conn, $item, $_group, $_gid, $obje_ids);
                }
            }
            foreach ($_refn as $item) {
                if ($item) {
                    Refn::read($conn, $item, $_group, $_gid);
                }
            }
            foreach ($_sour as $item) {
                if ($item) {
                    SourRef::read($conn, $item, $_group, $_gid, $sour_ids, $obje_ids);
                }
            }
            foreach ($_slgs as $item) {
                if ($item) {
                    Slgs::read($conn, $item, $familie);
                }
            }
            foreach ($subm as $item) {
                if ($item) {
                    Subm::read($conn, $item, $_group, $_gid, $obje_ids);
                }
            }
            if ($chan) {
                Chan::read($conn, $chan, 'family', $familie->id);
            }
        }
    }
}
