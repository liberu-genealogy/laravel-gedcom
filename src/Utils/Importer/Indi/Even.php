<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\Indi;
use \App\PersonEvent;
class Even
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID
     * @var string
     */

    public static function read($conn, $even, $person)
    {
        $class_name = get_class($even); // example return: PhpGedcom\Record\Indi\Birt
        $path = explode('\\', $class_name); // explode: ['PhpGedcom\Record\Indi\','Birt']
        $class_name = array_pop($path); // Birt

        $person_id = $person->id;
        $type = $even->getType();
        $attr = $even->getAttr();
        $_date = $even->getDate();
        $date = \ModularSoftware\LaravelGedcom\Utils\Importer\Date::read($conn,$_date);
        $_plac = $even->getPlac();
        $plac = \ModularSoftware\LaravelGedcom\Utils\Importer\Indi\Even\Plac::read($conn, $_plac);


        $_phon = $even->getPhon();
        $phon = \ModularSoftware\LaravelGedcom\Utils\Importer\Phon::read($conn, $_phon);
        $_addr = $even->getAddr();
        $addr_id = \ModularSoftware\LaravelGedcom\Utils\Importer\Addr::read($conn, $_addr);

        $caus = $even->getCaus();
        $age = $even->getAge();
        $agnc = $even->getAgnc();
        $adop = '';
        $adop_famc = '';
        $birt_famc = '';
        switch($class_name) {
            case 'Adop':
                $adop = $even->getAdop();
                $adop_famc = $even->getFamc();
            break;
            case 'Birt':
                $birt_famc = $even->getFamc();
            break;
            case 'Bapm':
            break;
            case 'Barm':
            break;
            case 'Basm':
            break;
            case 'Bles':
            break;
            case 'Buri':
            break;
            case 'Cast':
            break;
            case 'Cens':
            break;
            case 'Chr':
                $chr_famc = $even->getFamc();
            break;
            case 'Chra':
            break;
            case 'Conf':
            break;
            case 'Crem':
            break;
            case 'Dscr':
            break;
            case 'Deat':
            break;
            case 'Educ':
            break;
            case 'Emig':
            break;
            case 'Fcom':
            break;
            case 'Grad':
            break;
            case 'Idno':
            break;
            case 'Immi':
            break;
            case 'Nati':
            break;
            case 'Nchi':
            break;
            case 'Natu':
            break;
            case 'Nmr':
            break;
            case 'Occu':
            break;
            case 'Ordn':
            break;
            case 'Reti':
            break;
            case 'Prob':
            break;
            case 'Prop':
            break;
            case 'Reli':
            break;
            case 'Resi':
            break;
            case 'Ssn':
            break;
            case 'Titl':
            break;
            case 'Will':
            break;
            case 'Even':
            break;
            default:
        }
        $adop = '';
        $adop_famc = '';
        $birt_famc = '';
        // store Even
        $key =[
            'person_id'=>$person_id,
            'title' => $class_name,
            'type' => $type,
            'attr' => $attr,
            'date' => $date,
            'plac' => $plac,
            'phon' => $phon,
            'caus' => $caus,
            'age'  => $age,
            'agnc' => $agnc,
            'adop' => $adop,
            'adop_famc' => $adop_famc,
            'birt_famc' => $birt_famc,
        ];
        $data = [
            'person_id'=>$person_id,
            'title' => $class_name,
            'type' => $type, //
            'attr' => $attr, //
            'date' => $date,
            'plac' => $plac, //
            'addr_id' => $addr_id, //
            'phon' => $phon, //
            'caus' => $caus, //
            'age'  => $age, //
            'agnc' => $agnc, //
            'adop' => $adop, //
            'adop_famc' => $adop_famc,  //
            'birt_famc' => $birt_famc,  //
        ];
        $record = PersonEvent::on($conn)->updateOrCreate($key, $data);

        $_group = 'indi_even';
        $_gid = $record->id;

        // update person's record
        if ($class_name == 'Birt' && ! empty($date)) {
            $person->birthday = date('Y-m-d', strtotime($date));
        }
        // add deathyear to person table ( for form builder )
        if ($class_name == 'Deat' && ! empty($date)) {
            $person->deathday = date('Y-m-d', strtotime($date));
        }
        $person->save();

        // array
        $sour = $even->getSour();
        if($sour && count($sour) > 0) {
            foreach($sour as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\SourRef::read($conn, $item, $_group, $_gid);
                }
            }
        }
        $obje = $even->getObje();
        if($obje && count($obje) > 0) {
            foreach($obje as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\ObjeRef::read($conn, $item, $_group, $_gid);
                }
            }
        }
        $notes = $even->getNote();
        if($notes && count($notes) > 0) { 
            foreach($notes as $item) { 
                \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef::read($conn, $item, $_group, $_gid);
            }
        }
        // object
        $_chan = $even->getChan() ?? null;
        if($_chan !== null) {
            \ModularSoftware\LaravelGedcom\Utils\Importer\Chan::read($conn, $_chan, $_group, $_gid);
        }


        // $_type = $even->getType();
        // $person->addEvent($_type, $date, $plac);
    }
}
