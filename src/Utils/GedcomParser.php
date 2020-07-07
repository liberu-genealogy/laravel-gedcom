<?php

namespace ModularSoftware\LaravelGedcom\Utils;
use \App\Family;
use \App\Person;
use \App\Subn;
use \App\Subm;
use \App\Source;
use \App\SourRef;
use \App\Repo;
use \App\MediaObject;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use \App\Events\GedComProgressSent;
use \ModularSoftware\LaravelGedcom\Utils\Importer\Indi;
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
    protected $subm_ids = [];
    protected $conn = '';
    public function parse($conn, string $filename, string $slug, bool $progressBar = false)
    {
        $this->conn = $conn;
        error_log('+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++'.$conn);
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

        // store information about all the submitters to the GEDCOM file.
        foreach ($subm as $item){
            // $this->getSubm($item);
            if($item) {
                $_subm_id = $item->getSubm();
                $subm_id = \ModularSoftware\LaravelGedcom\Utils\Importer\Subm::read($this->conn,$item);
                $this->subm_ids[$_subm_id] = $subm_id;
            }
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        if($subn != null){
            // store the submission information for the GEDCOM file.
            // $this->getSubn($subn);
            \ModularSoftware\LaravelGedcom\Utils\Importer\Subn::read($this->conn, $subn, $this->subm_ids);
            if($progressBar === true){
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }


        // store sources cited throughout the GEDCOM file.
        foreach ($sour as $item){
            // $this->getSour($item);
            if($item) { 
                \ModularSoftware\LaravelGedcom\Utils\Importer\Sour::read($this->conn,$item);
            }
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }
        
        // store all the notes contained within the GEDCOM file that are not inline.
        foreach ($note as $item){
            // $this->getNote($item);
            if($item) {
                \ModularSoftware\LaravelGedcom\Utils\Importer\Note::read($this->conn,$item);
            }
            
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store all repositories that are contained within the GEDCOM file and referenced by sources.
        foreach ($repo as $item){
            // $this->getRepo($item);
            if($item) { 
                \ModularSoftware\LaravelGedcom\Utils\Importer\Repo::read($this->conn,$item);
            }
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                // event(new GedComProgressSent($slug, $total, $complete));
            }
        }
        // store all the media objects that are contained within the GEDCOM file.
        foreach ($obje as $item){
            // $this->getObje($item);
            if($item) { 
                \ModularSoftware\LaravelGedcom\Utils\Importer\Obje::read($this->conn,$item);
            }
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


        // array value
        $fams = $individual->getFams();  // self family, leave it now, note would be included in family 
        $famc = $individual->getFamc();  // parent family , leave it now, note and pedi would be included in family
        
        // added to database
        // string value
        $uid  = $individual->getUid();
        $chan = $individual->getChan();
        $rin  = $individual->getRin();
        $resn = $individual->getResn(); 
        $rfn  = $individual->getRfn();
        $afn  = $individual->getAfn();
        $sex = preg_replace("/[^MF]/", "", $individual->getSex());

        $attr = $individual->getAllAttr();
        $events = $individual->getAllEven();
        $note = $individual->getNote();
        $sour = $individual->getSour();
        $alia = $individual->getAlia(); // string array
        $asso = $individual->getAsso();
        $subm = $individual->getSubm();
        $anci = $individual->getAnci();
        $desi = $individual->getDesi();
        $refn = $individual->getRefn(); // \PhpGedcom\Record\Refn array
        $obje = $individual->getObje();

        // object
        $bapl = $individual->getBapl();
        $conl = $individual->getConl();
        $endl = $individual->getEndl();
        $slgc = $individual->getSlgc();

        if ($givn == "") {
            $givn = $name;
        }
        $config = json_encode(config('database.connections.'.$this->conn));
        $person = Person::on($this->conn)->updateOrCreate(compact('name', 'givn', 'surn', 'sex'), compact('name', 'givn', 'surn', 'sex', 'uid','chan', 'rin', 'resn', 'rfn', 'afn'));
        $this->persons_id[$g_id] = $person->id;
        if ($events !== null) {
            foreach ($events as $event) {
                if($event && count($event) > 0){
                    $e_data = $event[0];
                    \ModularSoftware\LaravelGedcom\Utils\Importer\Indi\Even::read($this->conn, $e_data, $person);
                }
            };
        }

        if ($attr !== null) {
            foreach ($attr as $event) {
                $e_data = $event[0];
                \ModularSoftware\LaravelGedcom\Utils\Importer\Indi\Even::read($this->conn,$e_data, $person);
            };
        }

        $_group = 'indi';
        $_gid= $person->id;
        if($note != null && count($note) > 0) {
            foreach($note as $item) {
                \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef::read($this->conn,$item, $_group, $_gid);
            }
        }
        if($sour !== null && count($sour) > 0) {
            foreach($sour as $item) {
                \ModularSoftware\LaravelGedcom\Utils\Importer\SourRef::read($this->conn,$item, $_group, $_gid);
            }
        }

        if($alia && count($alia) > 0) {
            foreach($alia as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\Indi\Alia::read($this->conn,$item, $_group, $_gid);
                }
            }
        }

        if($asso && count($asso) > 0) {
            foreach($asso as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\Indi\Asso::read($this->conn,$item, $_group, $_gid);
                }
            }
        }
        
        if($subm && count($subm) > 0) {
            foreach($subm as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\Indi\Subm::read($this->conn,$item, $_group, $_gid);
                }
            }
        }

        
        if($anci && count($anci) > 0) {
            foreach($anci as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\Indi\Anci::read($this->conn,$item, $_group, $_gid);
                }
            }
        }

        if($desi && count($desi) > 0) {
            foreach($desi as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\Indi\Desi::read($this->conn,$item, $_group, $_gid);
                }
            }
        }

        if($refn && count($refn) > 0) {
            foreach($refn as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\Refn::read($this->conn,$item, $_group, $_gid);
                }
            }
        }

        if($obje && count($obje) > 0) {
            foreach($obje as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\ObjeRef::read($this->conn,$item, $_group, $_gid);
                }
            }
        }
        
        if($bapl !== null) {
            \ModularSoftware\LaravelGedcom\Utils\Importer\Lds::read($this->conn,$bapl, $_group, $_gid, 'BAPL');
        }
        if($conl !== null) {
            \ModularSoftware\LaravelGedcom\Utils\Importer\Lds::read($this->conn,$conl, $_group, $_gid, 'CONL');
        }
        if($endl !== null) {
            \ModularSoftware\LaravelGedcom\Utils\Importer\Lds::read($this->conn,$endl, $_group, $_gid, 'ENDL');
        }
        if($slgc !== null) {
            \ModularSoftware\LaravelGedcom\Utils\Importer\Lds::read($this->conn,$slgc, $_group, $_gid, 'SLGC');
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
        $rin = $family->getRin();

        // array
        $subm = $family->getSubm();
        $_slgs = $family->getSlgs();

        $description = NULL;
        $type_id = 0;
        $children = $family->getChil();
        $events = $family->getAllEven();
        $_note = $family->getNote();
        $_obje = $family->getObje();
        $_sour = $family->getSour();
        $_refn = $family->getRefn();

        $husband_id = (isset($this->persons_id[$husb])) ? $this->persons_id[$husb] : 0;
        $wife_id = (isset($this->persons_id[$wife])) ? $this->persons_id[$wife] : 0;

        $family = Family::on($this->conn)->updateOrCreate(compact('husband_id', 'wife_id'), 
        compact('husband_id', 'wife_id', 'description', 'type_id' ,'chan', 'nchi', 'rin'));

        if ($children !== null) {
            foreach ($children as $child) {
                if (isset($this->persons_id[$child])) {
                    $person = Person::on($this->conn)->find($this->persons_id[$child]);
                    $person->child_in_family_id = $family->id;
                    $person->save();
                }
            }
        }

        if ($events !== null && count($events) > 0) {
            foreach ($events as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\Fam\Even::read($this->conn, $item, $family);
                }
                // $date = $this->getDate($item->getDate());
                // $place = $this->getPlace($item->getPlac());
                // $family->addEvent($item->getType(), $date, $place);
            };
        }
        $_group = 'fam';
        $_gid = $family->id;
        if($_note != null && count($_note) > 0) {
            foreach($_note as $item) {
                \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef::read($this->conn,$item, $_group, $_gid);
            }
        }
        if($_obje && count($_obje) > 0) {
            foreach($_obje as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\ObjeRef::read($this->conn,$item, $_group, $_gid);
                }
            }
        }
        if($_refn && count($_refn) > 0) {
            foreach($_refn as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\Refn::read($this->conn,$item, $_group, $_gid);
                }
            }
        }
        if($_sour && count($_sour) > 0) {
            foreach($_sour as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\SourRef::read($this->conn,$item, $_group, $_gid);
                }
            }
        }
        if($_slgs && count($_slgs) > 0) {
            foreach($_slgs as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\Fam\Slgs::read($this->conn,$item, $family);
                }
            }
        }
        if($subm && count($subm) > 0) {
            foreach($subm as $item) {
                if($item) {
                    \ModularSoftware\LaravelGedcom\Utils\Importer\Subm::read($this->conn,$item, $_group, $_gid);
                }
            }
        }
    }
}
