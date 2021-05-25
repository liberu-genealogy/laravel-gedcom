<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer\Fam;

use FamilyTree365\LaravelGedcom\Models\FamilyEvent;

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
        if ($even == null || $fam === null) {
            return;
        }
        $class_name = get_class($even);
        $type = $even->getType();
        $_date = $even->getDate();
        $date = \FamilyTree365\LaravelGedcom\Utils\Importer\Date::read($conn, $_date);
        if (strpos($date, 'BEF') !== false) {
            $newdate = trim(str_replace('BEF', '', $date));
            $date_cnvert = strtotime($newdate);
        } elseif (strpos($date, 'AFT') !== false) {
            $newdate = trim(str_replace('AFT', '', $date));
            $date_cnvert = strtotime($newdate);
        } else {
            $date_cnvert = strtotime($date);
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
            break;
            case 'Anul':
            break;
            case 'Cens':
            break;
            case 'Div':
            break;
            case 'Divf':
            break;
            case 'Enga':
            break;
            case 'Marr':
            break;
            case 'Marb':
            break;
            case 'Marc':
            break;
            case 'Marl':
            break;
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
        if ($sour && count($sour) > 0) {
            foreach ($sour as $item) {
                if ($item) {
                    \FamilyTree365\LaravelGedcom\Utils\Importer\SourRef::read($conn, $item, $_group, $_gid);
                }
            }
        }
        $obje = $even->getObje();
        if ($obje && count($obje) > 0) {
            foreach ($obje as $item) {
                if ($item) {
                    \FamilyTree365\LaravelGedcom\Utils\Importer\ObjeRef::read($conn, $item, $_group, $_gid, $obje_ids);
                }
            }
        }
        $notes = $even->getNote();
        if ($notes && count($notes) > 0) {
            foreach ($notes as $item) {
                \FamilyTree365\LaravelGedcom\Utils\Importer\NoteRef::read($conn, $item, $_group, $_gid);
            }
        }
    }
}
