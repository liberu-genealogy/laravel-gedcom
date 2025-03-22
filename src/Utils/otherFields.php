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

readonly class OtherFields
{
    public static function insertOtherFields(
        string $conn,
        array $individuals,
        array $objeIds,
        array $sourIds
    ): void {
        foreach ($individuals as $individual) {
            DB::transaction(function () use ($conn, $individual, $objeIds, $sourIds) {
                $person = self::findOrCreatePerson($conn, $individual);

                self::processEvents($conn, $individual, $person, $objeIds);
                self::processNotes($conn, $individual, $person);
                self::processSources($conn, $individual, $person, $sourIds, $objeIds);
                self::processMedia($conn, $individual, $person, $objeIds);
            });
        }
    }

    private static function findOrCreatePerson(string $conn, object $individual): Person
    {
        return app(Person::class)->on($conn)->firstOrCreate(
            ['name' => $individual->getName()?->first()?->getName()],
            self::extractPersonData($individual)
        );
    }

    private static function extractPersonData(object $individual): array
    {
        $names = $individual->getName();
        $name = current($names)->getName();
        $npfx = current($names)->getNpfx();
        $givn = current($names)->getGivn();
        $nick = current($names)->getNick();
        $spfx = current($names)->getSpfx();
        $surn = current($names)->getSurn();
        $nsfx = current($names)->getNsfx();
        $type = current($names)->getType();
        $sex = preg_replace('/[^MF]/', '', (string) $individual->getSex());
        $uid = $individual->getUid();
        $resn = $individual->getResn();
        $rin = $individual->getRin();
        $rfn = $individual->getRfn();
        $afn = $individual->getAfn();
        return compact('name', 'npfx', 'givn', 'nick', 'spfx', 'surn', 'nsfx', 'type', 'sex', 'uid', 'resn', 'rin', 'rfn', 'afn');
    }

    private static function processEvents(string $conn, object $individual, Person $person, array $objeIds): void
    {
        $events = $individual->getAllEven();
        if ($events !== null) {
            Even::read($conn, $events, $person, $objeIds);
        }
    }

    private static function processNotes(string $conn, object $individual, Person $person): void
    {
        $note = $individual->getNote();
        if ($note != null && (is_countable($note) ? count($note) : 0) > 0) {
            foreach ($note as $item) {
                if ($item) {
                    NoteRef::read($conn, $item, 'indi', $person->id ?? 0);
                }
            }
        }
    }

    private static function processSources(string $conn, object $individual, Person $person, array $sourIds, array $objeIds): void
    {
        $indv_sour = $individual->getSour();
        if ($indv_sour != null && (is_countable($indv_sour) ? count($indv_sour) : 0) > 0) {
            foreach ($indv_sour as $item) {
                if ($item) {
                    SourRef::read($conn, $item, 'indi', $person->id ?? 0, $sourIds, $objeIds);
                }
            }
        }
    }

    private static function processMedia(string $conn, object $individual, Person $person, array $objeIds): void
    {
        $obje = $individual->getObje();
        if ($obje != null) {
            foreach ($obje as $item) {
                if ($item) {
                    ObjeRef::read($conn, $item, 'indi', $person->id ?? 0, $objeIds);
                }
            }
        }
        $bapl = $individual->getBapl();
        if ($bapl != null) {
            foreach ($bapl as $item) {
                if ($item) {
                    Lds::read($conn, $item, 'indi', $person->id ?? 0, 'BAPL', $sourIds, $objeIds);
                }
            }
        }
        $conl = $individual->getConl();
        if ($conl != null) {
            foreach ($conl as $item) {
                if ($item) {
                    Lds::read($conn, $item, 'indi', $person->id ?? 0, 'CONL', $sourIds, $objeIds);
                }
            }
        }
        $endl = $individual->getEndl();
        if ($endl != null) {
            foreach ($endl as $item) {
                if ($item) {
                    Lds::read($conn, $item, 'indi', $person->id ?? 0, 'ENDL', $sourIds, $objeIds);
                }
            }
        }
        $slgc = $individual->getSlgc();
        if ($slgc != null) {
            foreach ($slgc as $item) {
                if ($item) {
                    Lds::read($conn, $item, 'indi', $person->id ?? 0, 'SLGC', $sourIds, $objeIds);
                }
            }
        }
    }

    private static function processAssociations(string $conn, object $individual, Person $person, array $objeIds): void
    {
        $asso = $individual->getAsso();
        if ($asso != null) {
            foreach ($asso as $item) {
                if ($item) {
                    Asso::read($conn, $item, 'indi', $person->id ?? 0);
                }
            }
        }
    }

    private static function processSubmissions(string $conn, object $individual, Person $person, array $objeIds): void
    {
        $subm = $individual->getSubm();
        if ($subm != null && (is_countable($subm) ? count($subm) : 0) > 0) {
            foreach ($subm as $item) {
                if ($item) {
                    Subm::read($conn, $item, 'indi', $person->id ?? 0, $objeIds);
                }
            }
        }
    }

    private static function processAncestors(string $conn, object $individual, Person $person, array $objeIds): void
    {
        $anci = $individual->getAnci();
        if ($anci != null && (is_countable($anci) ? count($anci) : 0) > 0) {
            foreach ($anci as $item) {
                if ($item) {
                    Anci::read($conn, $item, 'indi', $person->id ?? 0, $objeIds);
                }
            }
        }
    }

    private static function processReferences(string $conn, object $individual, Person $person, array $objeIds): void
    {
        $refn = $individual->getRefn();
        if ($refn != null) {
            foreach ($refn as $item) {
                if ($item) {
                    Refn::read($conn, $item, 'indi', $person->id ?? 0);
                }
            }
        }
    }
}