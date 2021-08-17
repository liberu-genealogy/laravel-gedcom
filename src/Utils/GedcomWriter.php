<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use FamilyTree365\LaravelGedcom\Events\GedComProgressSent;
use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Models\MediaObject;
use FamilyTree365\LaravelGedcom\Models\Note;
use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Models\Repository;
use FamilyTree365\LaravelGedcom\Models\Source;
use FamilyTree365\LaravelGedcom\Models\Subm;
use FamilyTree365\LaravelGedcom\Models\Subn;
use Gedcom\Parser;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class GedcomWriter
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID.
     *
     * @var string
     */
    protected $persons_id = [];

    public function parse(string $filename, string $slug, bool $progressBar = false)
    {
        $parser = new Parser();
        $gedcom = @$parser->parse($filename);

        /**
         * work.
         */
        $subn = $gedcom->getSubn();
        $subm = $gedcom->getSubm();
        $sour = $gedcom->getSour();
        $note = $gedcom->getNote();
        $repo = $gedcom->getRepo();
        $obje = $gedcom->getObje();

        /**
         * work end.
         */
        $c_subn = 0;
        $c_subm = count($subm);
        $c_sour = count($sour);
        $c_note = count($note);
        $c_repo = count($repo);
        $c_obje = count($obje);
        if ($subn != null) {
            //
            $c_subn = 1;
        }

        $individuals = $gedcom->getIndi();
        $families = $gedcom->getFam();
        $total = count($individuals) + count($families) + $c_subn + $c_subm + $c_sour + $c_note + $c_repo + $c_obje;
        $complete = 0;
        if ($progressBar === true) {
            $bar = $this->getProgressBar(count($individuals) + count($families));
            event(new GedComProgressSent($slug, $total, $complete));
        }

        if ($subn != null) {
            // store the submission information for the GEDCOM file.
            $this->getSubn($subn);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store information about all the submitters to the GEDCOM file.
        foreach ($subm as $item) {
            $this->getSubm($item);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store sources cited throughout the GEDCOM file.
        foreach ($sour as $item) {
            $this->getSour($item);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store all the notes contained within the GEDCOM file that are not inline.
        foreach ($note as $item) {
            $this->getNote($item);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store all repositories that are contained within the GEDCOM file and referenced by sources.
        foreach ($repo as $item) {
            $this->getRepo($item);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }
        // store all the media objects that are contained within the GEDCOM file.
        foreach ($obje as $item) {
            $this->getObje($item);
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
        $uid = $individual->getUid();
        $chan = $individual->getChan();
        $rin = $individual->getRin();
        $resn = $individual->getResn();
        $rfn = $individual->getRfn();
        $afn = $individual->getAfn();

        $sex = preg_replace('/[^MF]/', '', $individual->getSex());
        $attr = $individual->getAllAttr();
        $events = $individual->getAllEven();

        if ($givn == '') {
            $givn = $name;
        }
        $person = Person::query()->updateOrCreate(compact('name', 'givn', 'surn', 'sex'), compact('name', 'givn', 'surn', 'sex', 'uid', 'chan', 'rin', 'resn', 'rfn', 'afn'));
        $this->persons_id[$g_id] = $person->id;

        if ($events !== null) {
            foreach ($events as $event) {
                $date = $this->getDate($event->getDate());
                $place = $this->getPlace($event->getPlac());
                $person->addEvent($event->getType(), $date, $place);
            }
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
                $person->addEvent($event->getType(), $date, $place, $event->getAttr().' '.$note);
            }
        }
    }

    private function getFamily($family)
    {
        $husb = $family->getHusb();
        $wife = $family->getWife();

        // string
        $chan = $family->getChan();
        $nchi = $family->getNchi();

        $description = null;
        $type_id = 0;
        $children = $family->getChil();
        $events = $family->getAllEven();

        $husband_id = (isset($this->persons_id[$husb])) ? $this->persons_id[$husb] : 0;
        $wife_id = (isset($this->persons_id[$wife])) ? $this->persons_id[$wife] : 0;

        $family = Family::query()->updateOrCreate(compact('husband_id', 'wife_id'), compact('husband_id', 'wife_id', 'description', 'type_id', 'chan', 'nchi'));

        if ($children !== null) {
            foreach ($children as $child) {
                if (isset($this->persons_id[$child])) {
                    $person = Person::query()->find($this->persons_id[$child]);
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
            }
        }
    }

    private function getSubn($subn)
    {
        $subm = $subn->getSubm();
        $famf = $subn->getFamf();
        $temp = $subn->getTemp();
        $ance = $subn->getAnce();
        $desc = $subn->getDesc();
        $ordi = $subn->getOrdi();
        $rin = $subn->getRin();
        Subn::query()->updateOrCreate(compact('subm', 'famf', 'temp', 'ance', 'desc', 'ordi', 'rin'), compact('subm', 'famf', 'temp', 'ance', 'desc', 'ordi', 'rin'));
    }

    // insert subm data to model
    private function getSubm($_subm)
    {
        $subm = $_subm->getSubm() ?? 'Unknown'; // string
        $chan = $_subm->getChan() ?? ['Unknown']; // Record\Chan---
        $name = $_subm->getName() ?? 'Unknown'; // string
        if ($_subm->getAddr() != null) { // Record/Addr
         $addr = $_subm->getAddr();
            $addr->getAddr() ?? 'Unknown';
            $addr->getAdr1() ?? 'Unknown';
            $addr->getAdr2() ?? 'Unknown';
            $addr->getCity() ?? 'Unknown';
            $addr->getStae() ?? 'Unknown';
            $addr->getCtry() ?? 'Unknown';
        } else {
            $addr = null;
        }

        $rin = $_subm->getRin() ?? 'Unknown'; // string
        $rfn = $_subm->getRfn() ?? 'Unknown'; // string
        $_lang = $_subm->getLang() ?? ['Unknown']; // array
        $_phon = $_subm->getPhon() ?? ['Unknown']; // array
        $obje = $_subm->getObje() ?? ['Unknown']; // array ---

        // create chan model - id, ref_type (subm), date, time
        // create note model - id, ref_type ( chan ), note
        // create sour model - id, ref_type ( note), sour, Chan, titl, auth, data, text, publ, Repo, abbr, rin, refn_a, Note_a, Obje_a
        // $arr_chan = array('date'=>$chan->getDate(), 'time'=>$chan->getTime());
        // create obje model - id, _isRef, _obje, _form, _titl, _file, _Note_a

        if ($addr != null) {
            $arr_addr = [
                'addr' => $_addr->getAddr() ?? 'Unknown',
                'adr1' => $_addr->getAdr1() ?? 'Unknown',
                'adr2' => $_addr->getAdr2() ?? 'Unknown',
                'city' => $_addr->getCity() ?? 'Unknown',
                'stae' => $_addr->getStae() ?? 'Unknown',
                'ctry' => $_addr->getCtry() ?? 'Unknown',
            ];
        } else {
            $arr_addr = [
                'addr' => 'Unknown',
                'adr1' => 'Unknown',
                'adr2' => 'Unknown',
                'city' => 'Unknown',
                'stae' => 'Unknown',
                'ctry' => 'Unknown',
            ];
        }

        $addr = json_encode($arr_addr);
        $lang = json_encode($_lang);
        $arr_phon = [];
        foreach ($_phon as $item) {
            $__phon = $item->getPhon();
            array_push($arr_phon, $__phon);
        }
        $phon = json_encode($arr_phon);
        Subm::query()->updateOrCreate(compact('subm', 'name', 'addr', 'rin', 'rfn', 'lang', 'phon'), compact('subm', 'name', 'addr', 'rin', 'rfn', 'lang', 'phon'));
    }

    // insert sour data to database
    private function getSour($_sour)
    {
        $sour = $_sour->getSour(); // string
        $titl = $_sour->getTitl(); // string
        $auth = $_sour->getAuth(); // string
        $data = $_sour->getData(); // string
        $text = $_sour->getText(); // string
        $publ = $_sour->getPubl(); // string
        $abbr = $_sour->getAbbr(); // string
        Source::query()->updateOrCreate(compact('sour', 'titl', 'auth', 'data', 'text', 'publ', 'abbr'), compact('sour', 'titl', 'auth', 'data', 'text', 'publ', 'abbr'));
    }

    // insert note data to database
    private function getNote($_note)
    {
        $gid = $_note->getId(); // string
        $note = $_note->getNote(); // string
        $rin = $_note->getRin(); // string
        Note::query()->updateOrCreate(compact('gid', 'note', 'rin'), compact('gid', 'note', 'rin'));
    }

    // insert repo data to database
    private function getRepo($_repo)
    {
        $repo = $_repo->getRepo(); // string
        $name = $_repo->getName(); // string
        $_addr = $_repo->getAddr(); // Record/Addr
        $rin = $_repo->getRin(); // string
        $_phon = $_repo->getPhon(); // array
        $arr_addr = [
            'addr' => $_addr->getAddr(),
            'adr1' => $_addr->getAdr1(),
            'adr2' => $_addr->getAdr2(),
            'city' => $_addr->getCity(),
            'stae' => $_addr->getStae(),
            'ctry' => $_addr->getCtry(),
        ];
        $addr = json_encode($arr_addr);
        $arr_phon = [];
        foreach ($_phon as $item) {
            $__phon = $item->getPhon();
            array_push($arr_phon, $__phon);
        }
        $phon = json_encode($arr_phon);
        Repository::query()->updateOrCreate(compact('repo', 'name', 'addr', 'rin', 'phon'), compact('repo', 'name', 'addr', 'rin', 'phon'));
    }

    // insert obje data to database
    private function getObje($_obje)
    {
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
