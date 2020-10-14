<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils;

use GenealogiaWebsite\LaravelGedcom\Models\Family;
use GenealogiaWebsite\LaravelGedcom\Models\Person;

class ParentData
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

    public static function getPerson($conn, $individual, $obje_ids)
    {
        $g_id = $individual->getId();
        $name = '';
        $givn = '';
        $surn = '';
        $name = '';
        $npfx = '';
        $givn = '';
        $nick = '';
        $spfx = '';
        $surn = '';
        $nsfx = '';
        $type = '';
        $fone = null; // PhpGedcom/
        $romn = null;
        $names = $individual->getName();

        if (!empty($names)) {
            $name = current($names)->getName();
            $npfx = current($names)->getNpfx();
            $givn = current($names)->getGivn();
            $nick = current($names)->getNick();
            $spfx = current($names)->getSpfx();
            $surn = current($names)->getSurn();
            $nsfx = current($names)->getNsfx();
            $type = current($names)->getType();
        }

        // array value
        $fams = $individual->getFams();  // self family, leave it now, note would be included in family
        $famc = $individual->getFamc();  // parent family , leave it now, note and pedi would be included in family

        // added to database
        // string value
        $sex = preg_replace('/[^MF]/', '', $individual->getSex());
        $uid = $individual->getUid();
        $resn = $individual->getResn();
        $rin = $individual->getRin();
        $rfn = $individual->getRfn();
        $afn = $individual->getAfn();
        $attr = $individual->getAllAttr();
        $events = $individual->getAllEven();
        $note = $individual->getNote();
        $indv_sour = $individual->getSour();
        $alia = $individual->getAlia(); // string array
        $asso = $individual->getAsso();
        $subm = $individual->getSubm();
        $anci = $individual->getAnci();
        // $desi = $individual->getDesi();
        $refn = $individual->getRefn(); //
        $obje = $individual->getObje();
        // object
        $bapl = $individual->getBapl();
        $conl = $individual->getConl();
        $endl = $individual->getEndl();
        $slgc = $individual->getSlgc();

        $chan = $individual->getChan();

        if ($givn == '') {
            $givn = $name;
        }
        $config = json_encode(config('database.connections.'.$conn));

        $person = Person::on($conn)->updateOrCreate(compact('name', 'givn', 'surn', 'sex'), compact('name', 'givn', 'surn', 'sex', 'uid', 'rin', 'resn', 'rfn', 'afn'));
        $persons_id[$g_id] = $person->id;

        if ($events !== null) {
            foreach ($events as $event) {
                if ($event && count($event) > 0) {
                    $e_data = $event[0];
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Even::read($conn, $e_data, $person, $obje_ids);
                }
            }
        }

        if ($attr !== null) {
            foreach ($attr as $event) {
                $e_data = $event[0];
                \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Even::read($conn, $e_data, $person);
            }
        }

        $_group = 'indi';
        $_gid = $person->id;
        if ($names != null && count($names) > 0) {
            foreach ($names as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Name::read($conn, $item, $_group, $_gid);
                }
            }
        }

        if ($note != null && count($note) > 0) {
            foreach ($note as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\NoteRef::read($conn, $item, $_group, $_gid);
                }
            }
        }

        if ($indv_sour != null && count($indv_sour) > 0) {
            foreach ($indv_sour as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\SourRef::read($conn, $item, $_group, $_gid, $sour_ids, $obje_ids);
                }
            }
        }

        // ??
        if ($alia && count($alia) > 0) {
            foreach ($alia as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Alia::read($conn, $item, $_group, $_gid);
                }
            }
        }

        if ($asso && count($asso) > 0) {
            foreach ($asso as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Asso::read($conn, $item, $_group, $_gid);
                }
            }
        }

        if ($subm && count($subm) > 0) {
            foreach ($subm as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Subm::read($conn, $item, $_group, $_gid, $subm_ids);
                }
            }
        }

        if ($anci && count($anci) > 0) {
            foreach ($anci as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Anci::read($conn, $item, $_group, $_gid, $subm_ids);
                }
            }
        }

        // if ($desi && count($desi) > 0) {
        //     foreach ($desi as $item) {
        //         if ($item) {
        //             \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Desi::read($conn, $item, $_group, $_gid, $subm_ids);
        //         }
        //     }
        // }

        if ($refn && count($refn) > 0) {
            foreach ($refn as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Refn::read($conn, $item, $_group, $_gid);
                }
            }
        }

        if ($obje && count($obje) > 0) {
            foreach ($obje as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\ObjeRef::read($conn, $item, $_group, $_gid, $obje_ids);
                }
            }
        }

        if ($bapl && count($bapl) > 0) {
            foreach ($bapl as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Lds::read($conn, $item, $_group, $_gid, 'BAPL', $sour_ids, $obje_ids);
                }
            }
        }

        if ($conl && count($conl) > 0) {
            foreach ($conl as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Lds::read($conn, $item, $_group, $_gid, 'CONL', $sour_ids, $obje_ids);
                }
            }
        }

        if ($endl && count($endl) > 0) {
            foreach ($endl as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Lds::read($conn, $item, $_group, $_gid, 'ENDL', $sour_ids, $obje_ids);
                }
            }
        }

        if ($slgc && count($slgc) > 0) {
            foreach ($slgc as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Lds::read($conn, $item, $_group, $_gid, 'SLGC', $sour_ids, $obje_ids);
                }
            }
        }
        if ($chan) {
            \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Chan::read($conn, $chan, $_group, $_gid);
        }
    }
}
