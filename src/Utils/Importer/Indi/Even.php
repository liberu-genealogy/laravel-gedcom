<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer\Indi;

use FamilyTree365\LaravelGedcom\Models\PersonEvent;
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
    public static function read($conn, $events, $person, $obje_ids = [])
    {
        if (empty($person)) {
            return;
        }

        $eventData = [];
        foreach ($events as $event) {
            if ($event && (is_countable($event) ? count($event) : 0) > 0) {
                $even = $event[0];
                $class_name = $even::class;
                $person_id = $person->id;
                $type = $even->getType();
                $attr = $even->getAttr();
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
                //$addr_id = \FamilyTree365\LaravelGedcom\Utils\Importer\Addr::read($conn, $_addr);
                $addr_id = empty($_addr) ? null : \FamilyTree365\LaravelGedcom\Utils\Importer\Addr::read($conn, $_addr);

                $caus = $even->getCaus();
                $age = $even->getAge();
                $agnc = $even->getAgnc();
                $adop = '';
                $adop_famc = '';
                $birt_famc = '';
                switch ($class_name) {
                case 'Adop':
                    $adop = $even->getAdop();
                    $adop_famc = $even->getFamc();
                break;
                case 'Birt':
                    $birt_famc = $even->getFamc();
                break;
                case 'Bapm':
                case 'Barm':
                case 'Basm':
                case 'Bles':
                case 'Buri':
                case 'Cast':
                case 'Cens':
                break;
                case 'Chr':
                    $chr_famc = $even->getFamc();
                break;
                case 'Chra':
                case 'Conf':
                case 'Crem':
                case 'Dscr':
                case 'Deat':
                case 'Educ':
                case 'Emig':
                case 'Fcom':
                case 'Grad':
                case 'Idno':
                case 'Immi':
                case 'Nati':
                case 'Nchi':
                case 'Natu':
                case 'Nmr':
                case 'Occu':
                case 'Ordn':
                case 'Reti':
                case 'Prob':
                case 'Prop':
                case 'Reli':
                case 'Resi':
                case 'Ssn':
                case 'Titl':
                case 'Will':
                case 'Even':
                break;
                default:
            }
                $adop = '';
                $adop_famc = '';
                $birt_famc = '';
                // store Even
                $key = [
                    ['person_id', $person_id],
                    ['title', $class_name],
                    ['type', $type],
                    ['attr', $attr],
                    ['date', $date],
                    ['plac', $plac],
                    ['phon', $phon],
                    ['caus', $caus],
                    ['age', $age],
                    ['agnc', $agnc],
                    ['adop', $adop],
                    ['adop_famc', $adop_famc],
                    ['birt_famc', $birt_famc],
                ];
                $check = PersonEvent::on($conn)->where($key)->first();
                if (empty($check)) {
                    $data = [
                        'person_id'      => $person_id,
                        'title'          => $class_name,
                        'type'           => $type, //
                        'attr'           => $attr, //
                        'date'           => $date,
                        'converted_date' => $date_cnvert,
                        'plac'           => $plac, //
                        'addr_id'        => $addr_id, //
                        'phon'           => $phon, //
                        'caus'           => $caus, //
                        'age'            => $age, //
                        'agnc'           => $agnc, //
                        'adop'           => $adop, //
                        'adop_famc'      => $adop_famc,  //
                        'birt_famc'      => $birt_famc,  //
                    ];

                    $eventData[] = $data;
                }
            }
        }
        PersonEvent::on($conn)->insert($eventData);
        $new = new Even();
        $new->otherField($conn, $events, $person);
    }

    public static function otherField($conn, $events, $person)
    {
        try {
            foreach ($events as $event) {
                if ($event && (is_countable($event) ? count($event) : 0) > 0) {
                    $even = $event[0];
                    $class_name = $even::class;
                    $person_id = $person->id;
                    $type = $even->getType();
                    $attr = $even->getAttr();
                    $_date = $even->getDate();
                    $date = \FamilyTree365\LaravelGedcom\Utils\Importer\Date::read($conn, $_date);
                    $_plac = $even->getPlac();
                    $plac = \FamilyTree365\LaravelGedcom\Utils\Importer\Indi\Even\Plac::read($conn, $_plac);
                    $_phon = $even->getPhon();
                    $phon = \FamilyTree365\LaravelGedcom\Utils\Importer\Phon::read($conn, $_phon);
                    $_addr = $even->getAddr();
                    //$addr_id = \FamilyTree365\LaravelGedcom\Utils\Importer\Addr::read($conn, $_addr);
                    $addr_id = empty($_addr) ? null : \FamilyTree365\LaravelGedcom\Utils\Importer\Addr::read($conn, $_addr);

                    $caus = $even->getCaus();
                    $age = $even->getAge();
                    $agnc = $even->getAgnc();
                    $adop = '';
                    $adop_famc = '';
                    $birt_famc = '';
                    switch ($class_name) {
                    case 'Adop':
                        $adop = $even->getAdop();
                        $adop_famc = $even->getFamc();
                    break;
                    case 'Birt':
                        $birt_famc = $even->getFamc();
                    break;
                    case 'Bapm':
                    case 'Barm':
                    case 'Basm':
                    case 'Bles':
                    case 'Buri':
                    case 'Cast':
                    case 'Cens':
                    break;
                    case 'Chr':
                        $chr_famc = $even->getFamc();
                    break;
                    case 'Chra':
                    case 'Conf':
                    case 'Crem':
                    case 'Dscr':
                    case 'Deat':
                    case 'Educ':
                    case 'Emig':
                    case 'Fcom':
                    case 'Grad':
                    case 'Idno':
                    case 'Immi':
                    case 'Nati':
                    case 'Nchi':
                    case 'Natu':
                    case 'Nmr':
                    case 'Occu':
                    case 'Ordn':
                    case 'Reti':
                    case 'Prob':
                    case 'Prop':
                    case 'Reli':
                    case 'Resi':
                    case 'Ssn':
                    case 'Titl':
                    case 'Will':
                    case 'Even':
                    break;
                    default:
                }
                    $adop = '';
                    $adop_famc = '';
                    $birt_famc = '';
                    // store Even
                    $key = [
                        ['person_id', $person_id],
                        ['title', $class_name],
                        ['type', $type],
                        ['attr', $attr],
                        ['date', $date],
                        ['plac', $plac],
                        ['phon', $phon],
                        ['caus', $caus],
                        ['age', $age],
                        ['agnc', $agnc],
                        ['adop', $adop],
                        ['adop_famc', $adop_famc],
                        ['birt_famc', $birt_famc],
                    ];

                    // update person's record
                    if ($class_name == 'BIRT' && !empty($date)) {
                        $person->birthday = date('Y-m-d', strtotime((string) $date));
                    }
                    // add deathyear to person table ( for form builder )
                    if ($class_name == 'DEAT' && !empty($date)) {
                        $person->deathday = date('Y-m-d', strtotime((string) $date));
                    }
                    $person->save();

                    $sour = $even->getSour();
                    $notes = $even->getNote();
                    $obje = $even->getObje();
                    $_chan = $even->getChan() ?? null;
                    if ((!empty($sour) && (is_countable($sour) ? count($sour) : 0) > 0) || (!empty($obje) && (is_countable($obje) ? count($obje) : 0) > 0) || (!empty($notes) && (is_countable($notes) ? count($notes) : 0) > 0) || !empty($_chan)) {
                        $record = PersonEvent::on($conn)->where($key)->first();
                        $_group = 'indi_even';
                        $_gid = $record->id;
                    }

                    // array
                    //$sour = $even->getSour();
                    foreach ($sour as $item) {
                        if ($item) {
                            \FamilyTree365\LaravelGedcom\Utils\Importer\SourRef::read($conn, $item, $_group, $_gid);
                        }
                    }
                    //$obje = $even->getObje();
                    foreach ($obje as $item) {
                        if ($item) {
                            \FamilyTree365\LaravelGedcom\Utils\Importer\ObjeRef::read($conn, $item, $_group, $_gid, $obje);
                        }
                    }
                    //$notes = $even->getNote();
                    foreach ($notes as $item) {
                        \FamilyTree365\LaravelGedcom\Utils\Importer\NoteRef::read($conn, $item, $_group, $_gid);
                    }
                    // object
                    //$_chan = $even->getChan() ?? null;
                    if ($_chan !== null) {
                        \FamilyTree365\LaravelGedcom\Utils\Importer\Chan::read($conn, $_chan, $_group, $_gid);
                    }

                    // $_type = $even->getType();
                // $person->addEvent($_type, $date, $plac);
                }
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
