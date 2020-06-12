<?php

namespace ModularSoftware\LaravelGedcom\Utils;

use \App\Family;
use \App\Person;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use \App\Events\GedComProgressSent;
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

        if($subn != null){
            // 
            $c_subn = 1;
        }



        $individuals = $gedcom->getIndi();
        $families = $gedcom->getFam();
        $total = count($individuals) + count($families) + $c_subn+ $c_subm + $c_sour;
        $complete = 0;
        if ($progressBar === true) {
            $bar = $this->getProgressBar(count($individuals) + count($families));
            event(new GedComProgressSent($slug, $total, $complete));
        }

        if($subn != null){
            // store the submission information for the GEDCOM file.
            $this->getSubn($subn);
            if($progressBar === true){
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store information about all the submitters to the GEDCOM file.
        foreach ($subm as $item){
            $this->getSubm($item);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store sources cited throughout the GEDCOM file.
        foreach ($sour as $item){
            $this->getSour($item);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }
                
        foreach ($individuals as $individual) {
            $this->getPerson($individual);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        foreach ($families as $family) {
            $this->getFamily($family);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
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
        return "$input_date";
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
                $date = $this->getDate($event->getDate());
                $place = $this->getPlace($event->getPlac());
                $person->addEvent($event->getType(), $date, $place);
            };
        }

        if ($attr !== null) {
            foreach ($attr as $event) {
                $date = $this->getDate($event->getDate());
                $place = $this->getPlace($event->getPlac());
                if (count($event->getNote()) > 0) {
                    $note = current($event->getNote())->getNote();
                } else {
                    $note = '';
                }
                $person->addEvent($event->getType(), $date, $place, $event->getAttr() . ' ' . $note);
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
        $subm = $_subm->getSubm(); // string
        $chan = $_subm->getChan(); // Record\Chan---
        $name = $_subm->getName(); // string
        $_addr = $_subm->getAddr(); // Record/Addr
        $rin  = $_subm->getRin(); // string
        $rfn  = $_subm->getRfn(); // string 
        $_lang = $_subm->getLang(); // array
        $_phon = $_subm->getPhon(); // array
        $obje = $_subm->getObje(); // array ---

        // create chan model - id, ref_type (subm), date, time
        // create note model - id, ref_type ( chan ), note
        // create sour model - id, ref_type ( note), sour, Chan, titl, auth, data, text, publ, Repo, abbr, rin, refn_a, Note_a, Obje_a
        // $arr_chan = array('date'=>$chan->getDate(), 'time'=>$chan->getTime());
        // create obje model - id, _isRef, _obje, _form, _titl, _file, _Note_a

        $arr_addr = array(
            'addr'=>$_addr->getAddr(),
            'adr1' => $_addr->getAdr1(),
            'adr2'=>$_addr->getAdr2(),
            'city'=>$_addr->getCity(),
            'stae'=>$_addr->getStae(),
            'ctry'=>$_addr->getCtry()
        );
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
        $data = $_sour->getData(); // string
        $text = $_sour->getText(); // string
        $publ = $_sour->getPubl(); // string
        $repo = $_sour->getRepo(); // Repo
        $abbr = $_sour->getAbbr(); // string
        $rin = $_sour->getRin(); // string
        $refn = $_sour->getRefn(); // array
        $note = $_sour->getNote(); // array
        $obje = $_sour->getObje(); // array
        Sour::updateOrCreate(compact('sour', 'titl', 'auth', 'data', 'text', 'publ', 'abbr'), compact('sour', 'titl', 'auth', 'data', 'text', 'publ', 'abbr') );
    }
}
