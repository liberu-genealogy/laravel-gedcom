<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Utils\Importer\Anci;
use FamilyTree365\LaravelGedcom\Utils\Importer\Chan;
use FamilyTree365\LaravelGedcom\Utils\Importer\Indi\Alia;
use FamilyTree365\LaravelGedcom\Utils\Importer\Indi\Asso;
use FamilyTree365\LaravelGedcom\Utils\Importer\Indi\Desi;
use FamilyTree365\LaravelGedcom\Utils\Importer\Indi\Even;
use FamilyTree365\LaravelGedcom\Utils\Importer\Indi\Lds;
use FamilyTree365\LaravelGedcom\Utils\Importer\Indi\Name;
use FamilyTree365\LaravelGedcom\Utils\Importer\NoteRef;
use FamilyTree365\LaravelGedcom\Utils\Importer\ObjeRef;
use FamilyTree365\LaravelGedcom\Utils\Importer\Refn;
use FamilyTree365\LaravelGedcom\Utils\Importer\SourRef;
use FamilyTree365\LaravelGedcom\Utils\Importer\Subm;

class otherFields
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

    public static function insertOtherFields($conn, $individuals, $obje_ids, $sour_ids)
    {
        foreach ($individuals as $individual) {
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
            $fone = null; // Gedcom/
            $romn = null;
            $names = $individual->getName();
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
            $g_id = $individual->getId();

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

            if ($givn == '') {
                $givn = $name;
            }

            $person = Person::on($conn)->where('name', $name)->where('givn', $givn)->where('surn', $surn)->where('sex', $sex)->first();

            if ($events !== null) {
                Even::read($conn, $events, $person, $obje_ids);
                // foreach ($events as $event) {
                    //     if ($event && count($event) > 0) {
                    //         $e_data = $event[0];
                    //         Even::read($conn, $e_data, $person, $obje_ids);
                    //     }
                    // }
            }

            if ($attr !== null) {
                Even::read($conn, $attr, $person);
                // foreach ($attr as $event) {
                    //     $e_data = $event[0];
                    //     Even::read($conn, $e_data, $person);
                    // }
            }

            $_group = 'indi';
            $_gid = $person->id;
            if ($names != null && count($names) > 0) {
                // Name::read($conn, $names, $_group, $_gid);
                foreach ($names as $item) {
                    if ($item) {
                        Name::read($conn, $item, $_group, $_gid);
                    }
                }
            }

            if ($note != null && count($note) > 0) {
                // NoteRef::read($conn, $note, $_group, $_gid);
                foreach ($note as $item) {
                    if ($item) {
                        NoteRef::read($conn, $item, $_group, $_gid);
                    }
                }
            }

            if ($indv_sour != null && count($indv_sour) > 0) {
                // SourRef::read($conn, $indv_sour, $_group, $_gid, $sour_ids, $obje_ids);
                foreach ($indv_sour as $item) {
                    if ($item) {
                        SourRef::read($conn, $item, $_group, $_gid, $sour_ids, $obje_ids);
                    }
                }
            }

            // ??
            if ($alia && count($alia) > 0) {
                Alia::read($conn, $alia, $_group, $_gid);
                // foreach ($alia as $item) {
                    //     if ($item) {
                    //         Alia::read($conn, $item, $_group, $_gid);
                    //     }
                    // }
            }

            if ($asso && count($asso) > 0) {
                // Asso::read($conn, $item, $_group, $_gid);
                foreach ($asso as $item) {
                    if ($item) {
                        Asso::read($conn, $item, $_group, $_gid);
                    }
                }
            }

            if ($subm && count($subm) > 0) {
                Subm::read($conn, $subm, $_group, $_gid, $subm_ids);
                // foreach ($subm as $item) {
                    //     if ($item) {
                    //         Subm::read($conn, $item, $_group, $_gid, $subm_ids);
                    //     }
                    // }
            }

            if ($anci && count($anci) > 0) {
                Anci::read($conn, $anci, $_group, $_gid, $subm_ids);
                // foreach ($anci as $item) {
                    //     if ($item) {
                    //         Anci::read($conn, $item, $_group, $_gid, $subm_ids);
                    //     }
                    // }
            }

            // if ($desi && count($desi) > 0) {
            //     foreach ($desi as $item) {
            //         if ($item) {
            //             Desi::read($conn, $item, $_group, $_gid, $subm_ids);
            //         }
            //     }
            // }

            if ($refn && count($refn) > 0) {
                // Refn::read($conn, $refn, $_group, $_gid);
                foreach ($refn as $item) {
                    if ($item) {
                        Refn::read($conn, $item, $_group, $_gid);
                    }
                }
            }

            if ($obje && count($obje) > 0) {
                // ObjeRef::read($conn, $obje, $_group, $_gid, $obje_ids);
                foreach ($obje as $item) {
                    if ($item) {
                        ObjeRef::read($conn, $item, $_group, $_gid, $obje_ids);
                    }
                }
            }

            if ($bapl && count($bapl) > 0) {
                // Lds::read($conn, $bapl, $_group, $_gid, 'BAPL', $sour_ids, $obje_ids);
                foreach ($bapl as $item) {
                    if ($item) {
                        Lds::read($conn, $item, $_group, $_gid, 'BAPL', $sour_ids, $obje_ids);
                    }
                }
            }

            if ($conl && count($conl) > 0) {
                // Lds::read($conn, $conl, $_group, $_gid, 'CONL', $sour_ids, $obje_ids);
                foreach ($conl as $item) {
                    if ($item) {
                        Lds::read($conn, $item, $_group, $_gid, 'CONL', $sour_ids, $obje_ids);
                    }
                }
            }

            if ($endl && count($endl) > 0) {
                // Lds::read($conn, $endl, $_group, $_gid, 'ENDL', $sour_ids, $obje_ids);
                foreach ($endl as $item) {
                    if ($item) {
                        Lds::read($conn, $item, $_group, $_gid, 'ENDL', $sour_ids, $obje_ids);
                    }
                }
            }

            if ($slgc && count($slgc) > 0) {
                // Lds::read($conn, $slgc, $_group, $_gid, 'SLGC', $sour_ids, $obje_ids);

                foreach ($slgc as $item) {
                    if ($item) {
                        Lds::read($conn, $item, $_group, $_gid, 'SLGC', $sour_ids, $obje_ids);
                    }
                }
            }

            if ($chan) {
                Chan::read($conn, $chan, $_group, $_gid);
            }
        }
    }
}
