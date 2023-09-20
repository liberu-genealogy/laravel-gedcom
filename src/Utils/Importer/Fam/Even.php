<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer\Fam;

use FamilyTree365\LaravelGedcom\Models\FamilyEvent;
use Throwable;

class Even
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID.
     *
     * @var string
     */
    public static function read($conn, $even, $fam, $obje_ids = [])
    {
        try {
            if ($even == null || $fam === null) {
                return;
            }
            $class_name = $even::class;
            $type = $even->getType();
            $_date = $even->getDate();
            $date = \FamilyTree365\LaravelGedcom\Utils\Importer\Date::read($conn, $_date);
            if (str_contains((string) $date, 'BEF')) {
                $newdate = trim(str_replace('BEF', '', (string) $date));
                $date_cnvert = strtotime($newdate);
            } elseif (str_contains((string) $date, 'AFT')) {
                $newdate = trim(str_replace('AFT', '', (string) $date));
                $date_cnvert = strtotime($newdate);
            } else {
                $date_cnvert = strtotime((string) $date);
            }
            $_plac = $even->getPlac();
            $plac = \FamilyTree365\LaravelGedcom\Utils\Importer\Indi\Even\Plac::read($conn, $_plac);
            $_phon = $even->getPhon();
            $phon = \FamilyTree365\LaravelGedcom\Utils\Importer\Phon::read($conn, $_phon);
            $_addr = $even->getAddr();
            $addr_id = \FamilyTree365\LaravelGedcom\Utils\Importer\Addr::read($conn, $_addr);
            $caus = $even->getCaus();
            $age = $even->getAge();
            $agnc = $even->getAgnc();
            $fam_id = $fam->id;
            $husb_id = $fam->husband_id;
            $wife_id = $fam->wife_id;
            // update husb age
            $_husb = $even->getHusb();
            if ($_husb) {
                $husb = Person::on($conn)->find($husb_id);
                if ($husb) {
                    $husb->age = $_husb->getAge();
                    $husb->save();
                }
            }

            // update wife age
            $_wife = $even->getWife();
            if ($_wife) {
                $wife = Person::on($conn)->find($wife_id);
                if ($wife) {
                    $wife->age = $_wife->getAge();
                    $wife->save();
                }
            }

            switch ($class_name) {
                case 'Even':
                case 'Anul':
                case 'Cens':
                case 'Div':
                case 'Divf':
                case 'Enga':
                case 'Marr':
                case 'Marb':
                case 'Marc':
                case 'Marl':
                case 'Mars':
                break;
                default:
            }
            $adop = '';
            $adop_famc = '';
            $birt_famc = '';
            // store Fam/Even
            $key = [
                'family_id'      => $fam_id,
                'title'          => $class_name,
                'type'           => $type,
                'date'           => $date,
                'converted_date' => $date_cnvert,
                'plac'           => $plac,
                'phon'           => $phon,
                'caus'           => $caus,
                'age'            => $age,
                'agnc'           => $agnc,
                'husb'           => $husb_id,
                'wife'           => $wife_id,
            ];
            $data = [
                'family_id'      => $fam_id,
                'title'          => $class_name,
                'type'           => $type, //
                'date'           => $date,
                'converted_date' => $date_cnvert,
                'plac'           => $plac, //
                'addr_id'        => $addr_id, //
                'phon'           => $phon, //
                'caus'           => $caus, //
                'age'            => $age, //
                'agnc'           => $agnc, //
                'husb'           => $husb_id, //
                'wife'           => $wife_id, //
            ];

            $record = FamilyEvent::on($conn)->updateOrCreate($key, $data);

            $_group = 'fam_even';
            $_gid = $record->id;

            // array
            $sour = $even->getSour();
            foreach ($sour as $item) {
                if ($item) {
                    \FamilyTree365\LaravelGedcom\Utils\Importer\SourRef::read($conn, $item, $_group, $_gid);
                }
            }
            $obje = $even->getObje();
            foreach ($obje as $item) {
                if ($item) {
                    \FamilyTree365\LaravelGedcom\Utils\Importer\ObjeRef::read($conn, $item, $_group, $_gid, $obje_ids);
                }
            }
            $notes = $even->getNote();
            foreach ($notes as $item) {
                \FamilyTree365\LaravelGedcom\Utils\Importer\NoteRef::read($conn, $item, $_group, $_gid);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
