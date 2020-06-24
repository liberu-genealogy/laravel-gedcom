<?php

namespace ModularSoftware\LaravelGedcom\Utils;

use \App\Family;
use \App\Person;
use \App\Subn;
use \App\Subm;
use \App\Source;
use \App\Note;
use \App\Repository;
use \App\MediaObject;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use \App\Events\GedComProgressSent;
use Illuminate\Support\Facades\Log;

class GedcomParser
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID
     * @var string
     */
    protected $persons_id = [];

    public function parse(string $filename, string $slug, bool $progressBar = false)
    {
        $parser = new \PhpGedcom\Parser();
        $gedcom = @$parser->parse($filename);
        // var_dump($gedcom);

        /**
         * work
         */

        $head = $gedcom->getHead();
        $subn = $gedcom->getSubn();
        $subm = $gedcom->getSubm();
        $sour = $gedcom->getSour();
        $note = $gedcom->getNote();
        $repo = $gedcom->getRepo();
        $obje = $gedcom->getObje();

        /**
        * work end
        */
        $c_subn = 0;
        $c_subm = count($subm);
        $c_sour = count($sour);
        $c_note = count($note);
        $c_repo = count($repo);
        $c_obje = count($obje);
        if($subn != null){
            // 
            $c_subn = 1;
        }

        $individuals = $gedcom->getIndi();
        $families = $gedcom->getFam();
        $total = count($individuals) + count($families) + $c_subn+ $c_subm + $c_sour + $c_note + $c_repo + $c_obje;
        $complete = 0;
        if ($progressBar === true) {
            $bar = $this->getProgressBar(count($individuals) + count($families));
            // event(new GedComProgressSent($slug, $total, $complete));
        }
        Log::info('Individual:'.count($individuals));
        Log::info('Families:'.count($families));
        Log::info('Subn:'.$c_subn);
        Log::info('Subm:'.$c_subm);
        Log::info('Sour:'.$c_sour);
        Log::info('Note:'.$c_note);
        Log::info('Repo:'.$c_repo);
        if($subn != null){
            // store the submission information for the GEDCOM file.
            $this->getSubn($subn);
            if($progressBar === true){
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store information about all the submitters to the GEDCOM file.
        foreach ($subm as $item){
            $this->getSubm($item);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store sources cited throughout the GEDCOM file.
        foreach ($sour as $item){
            $this->getSour($item);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }
        
        // store all the notes contained within the GEDCOM file that are not inline.
        foreach ($note as $item){
            $this->getNote($item);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store all repositories that are contained within the GEDCOM file and referenced by sources.
        foreach ($repo as $item){
            $this->getRepo($item);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }
        // store all the media objects that are contained within the GEDCOM file.
        foreach ($obje as $item){
            $this->getObje($item);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }
        
        foreach ($individuals as $individual) {
            $this->getPerson($individual);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        foreach ($families as $family) {
            $this->getFamily($family);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        if ($progressBar === true) {
            $bar->finish();
        }
    }

    private function getProgressBar(int $max)
    {
        return (new OutputStyle(
            new StringInput(''),
            new StreamOutput(fopen('php://stdout', 'w'))
        ))->createProgressBar($max);
    }

    private function getDate($input_date)
    {
        if(is_object($input_date)){
            if(method_exists($input_date, 'getDate')) {
                return $input_date->getDate();
            }
        }else{
            return "$input_date";
        }
    }

    private function getPlace($place)
    {
        if (is_object($place)) {
            $place = $place->getPlac();
        }
        return $place;
    }

    private function getPerson($individual)
    {
        $g_id = $individual->getId();
        $name = '';
        $givn = '';
        $surn = '';

        if (!empty($individual->getName())) {
            $surn = current($individual->getName())->getSurn();
            $givn = current($individual->getName())->getGivn();
            $name = current($individual->getName())->getName();
        }

        // string value
        $uid  = $individual->getUid();
        $chan = $individual->getChan();
        $rin  = $individual->getRin();
        $resn = $individual->getResn(); 
        $rfn  = $individual->getRfn();
        $afn  = $individual->getAfn();

        // array value
        $note = $individual->getNote();
        $obje = $individual->getObje();
        $sour = $individual->getSour();
        $fams = $individual->getFams();
        $famc = $individual->getFamc();
        $alia = $individual->getAlia();
        $asso = $individual->getAsso();
        $subm = $individual->getSubm();
        $anci = $individual->getAnci();
        $desi = $individual->getDesi();
        $refn = $individual->getRefn();

        // object
        $bapl = $individual->getBapl();
        $conl = $individual->getConl();
        $endl = $individual->getEndl();
        $slgc = $individual->getSlgc();


        $sex = preg_replace("/[^MF]/", "", $individual->getSex());
        $attr = $individual->getAllAttr();
        $events = $individual->getAllEven();

        if ($givn == "") {
            $givn = $name;
        }
        $person = Person::updateOrCreate(compact('name', 'givn', 'surn', 'sex'), compact('name', 'givn', 'surn', 'sex', 'uid','chan', 'rin', 'resn', 'rfn', 'afn'));
        $this->persons_id[$g_id] = $person->id;

        if ($events !== null) {
            foreach ($events as $event) {
                if($event && count($event) > 0){
                    $e_data = $event[0];
                    $_date = $e_data->getDate();
                    $date = $this->getDate($_date);

                    $_plac = $e_data->getPlac();
                    $plac = $this->getPlace($_plac);

                    $_type = $e_data->getType();

                    $person->addEvent($_type, $date, $plac);
                }
            };
        }

        if ($attr !== null) {
            foreach ($attr as $event) {
                $e_data = $event[0];
                $_date = $e_data->getDate();
                $date = $this->getDate($_date);

                $_plac = $e_data->getPlac();
                $place = $this->getPlace($_plac);

                $_type = $e_data->getType();

                if (count($e_data->getNote()) > 0) {
                    $note = current($e_data->getNote())->getNote();
                } else {
                    $note = '';
                }
                $person->addEvent($_type, $date, $place, $e_data->getAttr() . ' ' . $note);
            };
        }
    }

    private function getFamily($family)
    {
        $g_id = $family->getId();
        $husb = $family->getHusb();
        $wife = $family->getWife();
        
        // string
        $chan = $family->getChan();
        $nchi = $family->getNchi();

        // array
        $_slgs = $family->getSlgs();
        $_subm = $family->getSubm();
        $_refn = $family->getRefn();
        $_rin = $family->getRin();
        $_note = $family->getNote();
        $_sour = $family->getSour();
        $_obje = $family->getObje();

        $description = NULL;
        $type_id = 0;
        $children = $family->getChil();
        $events = $family->getAllEven();

        $husband_id = (isset($this->persons_id[$husb])) ? $this->persons_id[$husb] : 0;
        $wife_id = (isset($this->persons_id[$wife])) ? $this->persons_id[$wife] : 0;

        $family = Family::updateOrCreate(compact('husband_id', 'wife_id'), compact('husband_id', 'wife_id', 'description', 'type_id' ,'chan', 'nchi'));

        if ($children !== null) {
            foreach ($children as $child) {
                if (isset($this->persons_id[$child])) {
                    $person = Person::find($this->persons_id[$child]);
                    $person->child_in_family_id = $family->id;
                    $person->save();
                }
            }
        }

        if ($events !== null) {
            foreach ($events as $event) {
                $date = $this->getDate($event->getDate());
                $place = $this->getPlace($event->getPlac());
                $family->addEvent($event->getType(), $date, $place);
            };
        }
    }

    private function getSubn($subn){
        $subm = $subn->getSubm();
        $famf = $subn->getFamf();
        $temp = $subn->getTemp();
        $ance = $subn->getAnce();
        $desc = $subn->getDesc();
        $ordi = $subn->getOrdi();
        $rin = $subn->getRin();
        $_subn = Subn::updateOrCreate(compact('subm', 'famf', 'temp', 'ance', 'desc','ordi', 'rin'), compact('subm', 'famf', 'temp', 'ance', 'desc','ordi', 'rin'));
    }

    // insert subm data to model
    private function getSubm($_subm){
        $subm = $_subm->getSubm() ?? 'Unknown'; // string
        $chan = $_subm->getChan() ?? array('Unknown'); // Record\Chan---
        $name = $_subm->getName() ?? 'Unknown'; // string
        if ($_subm->getAddr() != NULL) // Record/Addr
        {
            $addr = $_subm->getAddr();
            $addr->getAddr() ?? 'Unknown';
            $addr->getAdr1() ?? 'Unknown';
            $addr->getAdr2() ?? 'Unknown';
            $addr->getCity() ?? 'Unknown';
            $addr->getStae() ?? 'Unknown';
            $addr->getCtry() ?? 'Unknown';
        }
        else {
            $addr = NULL;
        }

        $rin  = $_subm->getRin() ?? 'Unknown'; // string
        $rfn  = $_subm->getRfn() ?? 'Unknown'; // string 
        $_lang = $_subm->getLang() ?? array('Unknown'); // array
        $_phon = $_subm->getPhon() ?? array('Unknown'); // array
        $obje = $_subm->getObje() ?? array('Unknown'); // array ---

        // create chan model - id, ref_type (subm), date, time
        // create note model - id, ref_type ( chan ), note
        // create sour model - id, ref_type ( note), sour, Chan, titl, auth, data, text, publ, Repo, abbr, rin, refn_a, Note_a, Obje_a
        // $arr_chan = array('date'=>$chan->getDate(), 'time'=>$chan->getTime());
        // create obje model - id, _isRef, _obje, _form, _titl, _file, _Note_a

        if ($addr != NULL)
        {
            $arr_addr = array(
                'addr'=>$addr->getAddr() ?? 'Unknown',
                'adr1' => $addr->getAdr1() ?? 'Unknown',
                'adr2'=>$addr->getAdr2() ?? 'Unknown',
                'city'=>$addr->getCity() ?? 'Unknown',
                'stae'=>$addr->getStae() ?? 'Unknown',
                'ctry'=>$addr->getCtry() ?? 'Unknown'
            );
        } else {
            $arr_addr = array(
                'addr'=> 'Unknown',
                'adr1' => 'Unknown',
                'adr2'=> 'Unknown',
                'city'=> 'Unknown',
                'stae'=> 'Unknown',
                'ctry'=> 'Unknown'
            );
        }
        $addr = json_encode($arr_addr);
        $lang = json_encode($_lang);
        $arr_phon = array();
        foreach($_phon as $item){
            $__phon = $item->getPhon();
            array_push($arr_phon, $__phon);
        }
        $phon = json_encode($arr_phon);
        Subm::updateOrCreate(compact('subm', 'name','addr','rin','rfn','lang','phon'), compact('subm', 'name','addr','rin','rfn','lang','phon'));
    }
    // insert sour data to database
    private function getSour($_sour){
        $sour = $_sour->getSour(); // string
        $chan = $_sour->getChan(); // Record/Chan
        $titl = $_sour->getTitl(); // string
        $auth = $_sour->getAuth(); // string
        $data = $_sour->getData(); // object
        $text = $_sour->getText(); // string
        $publ = $_sour->getPubl(); // string
        $repo = $_sour->getRepo(); // Repo
        $abbr = $_sour->getAbbr(); // string
        $rin = $_sour->getRin(); // string
        $refn = $_sour->getRefn(); // array
        $note = $_sour->getNote(); // array
        $obje = $_sour->getObje(); // array
        Source::updateOrCreate(compact('sour', 'titl', 'auth', 'text', 'publ', 'abbr'), compact('sour', 'titl', 'auth', 'text', 'publ', 'abbr') );
    }

    // insert note data to database
    private function getNote($_note){
        $gid = $_note->getId(); // string
        $note = $_note->getNote(); // string
        $chan = $_note->getChan(); // string ? 
        $even = $_note->getEven(); // string ? 
        $refn = $_note->getRefn(); // array
        $rin = $_note->getRin(); // string
        $sour = $_note->getSour(); // array
        Note::updateOrCreate(compact('gid','note', 'rin'), compact('gid','note', 'rin'));
    }

    // insert repo data to database
    private function getRepo($_repo){
        $repo = $_repo->getRepo(); // string 
        $name = $_repo->getName(); // string
        $_addr = $_repo->getAddr(); // Record/Addr
        $rin = $_repo->getRin(); // string
        $chan = $_repo->getChan(); // Record / Chan -- 
        $_phon = $_repo->getPhon(); // array
        $refn = $_repo->getRefn(); // array --
        $note = $_repo->getNote(); // array --
        $arr_addr = array(
            'addr'=>$_addr->getAddr(),
            'adr1' => $_addr->getAdr1(),
            'adr2'=>$_addr->getAdr2(),
            'city'=>$_addr->getCity(),
            'stae'=>$_addr->getStae(),
            'ctry'=>$_addr->getCtry()
        );
        $addr = json_encode($arr_addr);
        $arr_phon = array();
        foreach($_phon as $item){
            $__phon = $item->getPhon();
            array_push($arr_phon, $__phon);
        }
        $phon = json_encode($arr_phon);
        Repository::updateOrCreate(compact('repo', 'name', 'addr', 'rin', 'phon'), compact('repo', 'name', 'addr', 'rin', 'phon'));     
    }

    // insert obje data to database
    private function getObje($_obje){
        $gid = $_obje->getId(); // string
        $_form = $_obje->getForm(); // string
        $_titl = $_obje->getTitl(); // string
        $_blob = $_obje->getBlob(); // string
        $_rin = $_obje->getRin(); // string
        $_chan = $_obje->getChan(); // Chan
        $_file = $_obje->getFile(); // string
        MediaObject::updateOrCreate(compact('gid', 'form', 'titl', 'blob', 'rin', 'file'), compact('gid', 'form', 'titl', 'blob', 'rin', 'file'));
    }
}
