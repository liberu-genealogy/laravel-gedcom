<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils;

use GenealogiaWebsite\LaravelGedcom\Events\GedComProgressSent;
use GenealogiaWebsite\LaravelGedcom\Models\Family;
use GenealogiaWebsite\LaravelGedcom\Models\Person;
use GenealogiaWebsite\LaravelGedcom\Models\PersonAlia;
use GenealogiaWebsite\LaravelGedcom\Models\PersonAsso;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use DB;

class FamilyData
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

    public static function getFamily($conn,$family,$obje_ids)
    {
        $g_id = $family->getId();
        $resn = $family->getResn();
        $husb = $family->getHusb();
        $wife = $family->getWife();

        // string
        $nchi = $family->getNchi();
        $rin = $family->getRin();

        // array
        $subm = $family->getSubm();
        $_slgs = $family->getSlgs();

        $description = null;
        $type_id = 0;

        $children = $family->getChil();
        $events = $family->getAllEven();
        $_note = $family->getNote();
        $_obje = $family->getObje();
        $_sour = $family->getSour();
        $_refn = $family->getRefn();

        // object
        $chan = $family->getChan();

        $husband_id = (isset($persons_id[$husb])) ? $persons_id[$husb] : 0;
        $wife_id = (isset($persons_id[$wife])) ? $persons_id[$wife] : 0;

        $family = Family::on($conn)->updateOrCreate(
            compact('husband_id', 'wife_id'),
            compact('husband_id', 'wife_id', 'description', 'type_id', 'nchi', 'rin')
        );

        if ($children !== null) {
            foreach ($children as $child) {
                if (isset($persons_id[$child])) {
                    $person = Person::on($conn)->find($persons_id[$child]);
                    $person->child_in_family_id = $family->id;
                    $person->save();
                }
            }
        }

        if ($events !== null && count($events) > 0) {
            foreach ($events as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Fam\Even::read($conn, $item, $family, $obje_ids);
                }
                // $date = $getDate($item->getDate());
                // $place = $getPlace($item->getPlac());
                // $family->addEvent($item->getType(), $date, $place);
            }
        }
        $_group = 'fam';
        $_gid = $family->id;
        if ($_note != null && count($_note) > 0) {
            foreach ($_note as $item) {
                \GenealogiaWebsite\LaravelGedcom\Utils\Importer\NoteRef::read($conn, $item, $_group, $_gid);
            }
        }
        if ($_obje && count($_obje) > 0) {
            foreach ($_obje as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\ObjeRef::read($conn, $item, $_group, $_gid, $obje_ids);
                }
            }
        }
        if ($_refn && count($_refn) > 0) {
            foreach ($_refn as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Refn::read($conn, $item, $_group, $_gid);
                }
            }
        }
        if ($_sour && count($_sour) > 0) {
            foreach ($_sour as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\SourRef::read($conn, $item, $_group, $_gid, $sour_ids, $obje_ids);
                }
            }
        }
        if ($_slgs && count($_slgs) > 0) {
            foreach ($_slgs as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Fam\Slgs::read($conn, $item, $family);
                }
            }
        }
        if ($subm && count($subm) > 0) {
            foreach ($subm as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Subm::read($conn, $item, $_group, $_gid, $obje_ids);
                }
            }
        }
        if ($chan) {
            \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Chan::read($conn, $chan, 'family', $family->id);
        }
    }
}
