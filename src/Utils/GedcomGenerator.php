<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use Carbon\Carbon;
use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Models\PersonEvent;
use FamilyTree365\LaravelGedcom\Models\Source;
use Gedcom\Gedcom;
use Gedcom\Record\Fam;
use Gedcom\Record\Fam\Even;
use Gedcom\Record\Fam\Slgs;
use Gedcom\Record\Head;
use Gedcom\Record\Head\Sour;
use Gedcom\Record\Indi;
use Gedcom\Record\Indi\Even as Personal;
use Gedcom\Record\Indi\Fams;
use Gedcom\Record\Indi\Name;
use Gedcom\Record\NoteRef;
use Gedcom\Record\ObjeRef;
use Gedcom\Record\SourRef;
use Gedcom\Record\Subm;
use Gedcom\Writer;

class GedcomGenerator
{
    protected $arr_indi_id = [];
    protected $arr_fam_id = [];
    protected $_gedcom;
    protected $log = "\n";

    /**
     * Constructor with family_id.
     *
     * @param int $p_id the         primary key of table `people`
     * @param int $family_id        Primary key of table `families`
     * @param int $up_nest
     * @param int $down_nest
     */
    public function __construct(protected $p_id = 0, protected $family_id = 0, protected $up_nest = 0, protected $down_nest = 0)
    {
        $this->_gedcom = new Gedcom();
    }

    public function getGedcomFamily()
    {
        $this->setHead();
        $writer = new Writer();

        return $writer->convert($this->_gedcom);
    }

    public function getGedcomPerson()
    {
        $this->setHead();
        $this->addUpData($this->p_id);
        $writer = new Writer();

        return $writer->convert($this->_gedcom);
    }

    public function addUpData($p_id, $nest = 0, $processed_ids = [])
    {
        // Prevent infinite recursion by tracking processed IDs
        if (in_array($p_id, $processed_ids)) {
            return;
        }
        $processed_ids[] = $p_id;

        if ($this->up_nest < $nest) {
            return;
        }

        // Process in batches to reduce memory usage
        app(Person::class)->query()->where('id', $p_id)->chunk(100, function($persons) {
            foreach ($persons as $person) {
                if (!in_array($person->id, $this->arr_indi_id)) {
                    $this->arr_indi_id[] = $person->id;
                    $this->setIndi($person->id);
                }
            }
        });

        // process family ( partner, children )
        if ($p_id) {
            app(Family::class)->query()->where('husband_id', $p_id)->orwhere('wife_id', $p_id)->chunk(50, function($families) use ($nest, $processed_ids) {
                $this->processFamilies($families, $nest, $processed_ids);
            });
        } else {
            app(Family::class)->query()->chunk(50, function($families) use ($nest, $processed_ids) {
                $this->processFamilies($families, $nest, $processed_ids);
            });
        }

        $this->setSour();
    }

    /**
     * Process families in batches to reduce memory usage
     */
    private function processFamilies($families, $nest, $processed_ids)
    {
        foreach ($families as $item) {
            // add family
            $f_id = $item->id;
            if (!in_array($f_id, $this->arr_fam_id)) {
                $this->arr_fam_id[] = $f_id;
                $this->setFam($f_id);
            }

            // add partner to indi
            $husb_id = $item->husband_id;
            $wife_id = $item->wife_id;
            $this->log .= $nest.' f_id='.$f_id."\n";
            $this->log .= $nest.' husb_id='.$husb_id."\n";
            $this->log .= $nest.' wife_id='.$wife_id."\n";

            // Prevent infinite recursion by checking processed IDs
            // $this->addUpData($husb_id, $nest, $processed_ids);
            // $this->addUpData($wife_id, $nest, $processed_ids);

            // add children to indi in batches
            app(Person::class)->query()->where('child_in_family_id', $f_id)
                ->chunk(50, function($children) {
                    foreach ($children as $item2) {
                        $child_id = $item2->id;
                        if (!in_array($child_id, $this->arr_indi_id)) {
                            $this->arr_indi_id[] = $child_id;
                            $this->setIndi($child_id);
                        }
                    }
                });
        }
    }

    public function addDownData($p_id, $nest = 0)
    {
        if (empty($p_id) || $p_id < 1) {
            return;
        }
        if ($this->down_nest < $nest) {
            return;
        }

        $person = app(Person::class)->query()->find($p_id);
        if ($person == null) {
            return;
        }

        // process self
        if (!in_array($p_id, $this->arr_indi_id)) {
            // add to indi array
            $this->arr_indi_id[] = $p_id;
            $this->setIndi($p_id);
        }

        $_families = app(Family::class)->query()->where('husband_id', $p_id)->orwhere('wife_id', $p_id)->get();
        foreach ($_families as $item) {
            // add family
            $f_id = $item->id;
            if (!in_array($f_id, $this->arr_fam_id)) {
                $this->arr_fam_id[] = $f_id;
                $this->setFam($f_id);
            }
            // process partner
            $husband_id = $item->husband_id;
            $wife_id = $item->wife_id;
            $this->addDownData($husband_id, $nest);
            $this->addDownData($wife_id, $nest);

            // process child
            $children = app(Person::class)->query()->where('child_in_family_id', $item->id);
            foreach ($children as $item2) {
                $child_id = $item2->id;
                $nest_next = $nest + 1;
                $this->addDownData($child_id, $nest_next);
            }
        }

        // process parent
        $parent_family_id = $person->child_in_family_id;
        $parent_family = app(Family::class)->query()->find($parent_family_id);
        if ($parent_family != null) {
            $father_id = $parent_family->husband_id;
            $mother_id = $parent_family->wife_id;
            if (!in_array($father_id, $this->arr_indi_id)) {
                $this->arr_indi_id[] = $father_id;
                $this->setIndi($father_id);
            }
            if (!in_array($mother_id, $this->arr_indi_id)) {
                $this->arr_indi_id[] = $mother_id;
                $this->setIndi($mother_id);
            }
        }
        // process siblings
        $siblings = app(Person::class)->query()->where('child_in_family_id', $parent_family_id)->get();
        foreach ($siblings as $item3) {
            $sibling_id = $item3->id;
            if (!in_array($sibling_id, $this->arr_indi_id)) {
                $this->addDownData($sibling_id, $nest);
            }
        }
    }

    protected function setHead()
    {
        $head = new Head();
        $sour = new Sour();
        $sour->setSour(env('APP_NAME', ''));
        $sour->setVersion('1.0');
        $head->setSour($sour);
        $dest = null;
        $head->setDest($dest);
        $date = null;
        $head->setDate($date);
        $subm = null;
        $head->setSubm($subm);
        $subn = null;
        $head->setSubn($subn);
        $file = null;
        $head->setFile($file);
        $copr = null;
        $head->setCopr($copr);
        $gedc = null;
        $head->setGedc($gedc);
        $char = null;
        $head->setChar($char);
        $lang = null;
        $head->setLang($lang);
        $plac = null;
        $head->setPlac($plac);
        $note = null;
        $head->setNote($note);
        $this->_gedcom->setHead($head);
    }

    protected function setIndi($p_id)
    {
        $indi = new Indi();
        $person = app(Person::class)->query()->find($p_id);
        if ($person == null) {
            return;
        }
        $id = $person->id;
        $indi->setId($id);

        $gid = $person->gid;
        $indi->setGid($gid);

        $uid = $person->uid;
        $indi->setUid($uid);

        $_name = new Name();
        $_name->setName($person->name);
        $_name->setGivn($person->givn);
        $_name->setNick($person->nick);
        $_name->setSurn($person->surn);
        $_name->setNsfx($person->nsfx);
        $indi->addName($_name);

        $sex = $person->sex;
        $indi->setSex($sex);

        if ($person->birthday || $person->birth_year) {
            $birt = $person->birthday ? strtoupper((string) Carbon::createFromFormat('Y-m-d', $person->birthday)->format('j M Y')) : $person->birth_year;
            $indi->setBirt($birt);
        }

        if ($person->deathday || $person->death_year) {
            $deat = $person->deathday ? strtoupper((string) Carbon::createFromFormat('Y-m-d', $person->deathday)->format('j M Y')) : $person->death_year;
            $indi->setDeat($deat);
        }

        if ($person->burial_day || $person->burial_year) {
            $buri = $person->burial_day ? strtoupper((string) Carbon::parse($person->burial_day)->format('j M Y')) : $person->burial_year;
            $indi->setBuri($buri);
        }

        if ($person->chan) {
            $chan = Carbon::parse($person->chan);
            $chan = [
                strtoupper((string) $chan->format('j M Y')),
                $chan->format('H:i:s.v'),
            ];
            $indi->setChan($chan);
        }

        $place = app(PersonEvent::class)->query()->find($p_id);
        $_plac = new Personal();
        if (!empty($place->type)) {
            $_plac->setType($place->type);
        }
        if (!empty($place->date)) {
            $date = \FamilyTree365\LaravelGedcom\Utils\Importer\Date::read('', $place->date);
            $_plac->setDate($date);
        }
        if (!empty($place->type) && !empty($place->date)) {
            $indi->getAllEven();
        }

        $this->_gedcom->addIndi($indi);
    }

    protected function setFam($family_id)
    {
        $famData = app(Family::class)->query()->where('id', $family_id)->first();
        if ($famData == null) {
            return null;
        }
        $fam = new Fam();
        $_id = $famData->id;
        $fam->setId($_id);

        $_chan = null;
        $fam->setChan($_chan);

        $_husb = $famData->husband_id;
        $fam->setHusb($_husb);

        // add husb individual
        // $this->setIndi($_husb, $family_id);

        $_wife = $famData->wife_id;
        $fam->setWife($_wife);

        // add wife individual
        // $this->setIndi($_wife, $family_id);

        $_nchi = null;
        $fam->setNchi($_nchi);

        $_chil = app(Person::class)->query()->where('child_in_family_id', $family_id)->get();
        foreach ($_chil as $item) {
            $fam->addChil($item->id);
            // $this->setIndi($item->id);
        }

        $_even = [];
        foreach ($_even as $item) {
            $even = new Even();
            $_type = null; // string
            $_date = null; // string
            $_plac = null; // \Gedcom\Record\Indi\Even\Plac
            $_caus = null; // string
            $_age = null;  // string
            $_addr = null; // \Gedcom\Record\Addr
            $_phon = []; // \Gedcom\Record\Phon
            $_agnc = null; // string
            $_husb = null; // \Gedcom\Record\Fam\Even\Husb
            $_wife = null; // \Gedcom\Record\Fam\Even\Wife
            $_obje = []; // \Gedcom\Writer\ObjeRef
            $_sour = []; // \Gedcom\Writer\SourRef
            $_note = []; // \Gedcom\Writer\NoteRef
            $even->setType($_type);
            $even->setDate($_date);
            $even->setPlac($_plac);
            $even->setCaus($_caus);
            $even->setAddr($_addr);
            $even->setPhon($_phon);
            $even->setAgnc($_agnc);
            $even->setHusb($_husb);
            $even->setWife($_wife);
            $even->setObje($_obje);
            $even->setSour($_sour);
            $even->setNote($_note);
            $fam->addEven($even);
        }

        $_slgs = [];
        foreach ($_slgs as $item) {
            $slgs = new Slgs();
            $_stat = null;
            $_date = null;
            $_plac = null;
            $_temp = null;
            $_sour = [];
            $_note = [];

            $slgs->setStat($_stat);
            $slgs->setDate($_date);
            $slgs->setPlac($_plac);
            $slgs->setTemp($_temp);
            $slgs->setSour($_sour);
            $slgs->setNote($_note);
            $fam->addSlgs($slgs);
        }

        $_subm = [];
        foreach ($_subm as $item) {
            $subm = new Subm();
            $subm_id = null;
            $chan = null; // @var Record\Chan
            $name = null;
            $addr = null; //@var Record\Addr
            $rin = null;
            $rfn = null;
            $lang = [];
            $phon = [];
            $obje = [];
            $note = [];

            $subm->setSubm($subm_id);
            $subm->setChan($chan);
            $subm->setName($name);
            $subm->setAddr($addr);
            $subm->setRin($rin);
            $subm->setRfn($rfn);

            $subm->setLang($lang);
            $subm->setPhon($phon);
            $subm->setObje($obje);
            $subm->setNote($note);

            $fam->addSubm($subm);
        }

        $_refn = [];
        foreach ($_refn as $item) {
            $refn = null;
            $type = null;

            $subm->setRefn($refn);
            $subm->setType($type);

            $fam->addRefn($refn);
        }

        $_rin = null;
        $fam->setRin($_rin);

        $_note = [];
        foreach ($_note as $item) {
            $note = new NoteRef();
            $fam->addNote($note);
        }

        $_sour = Source::all();
        foreach ($_sour as $item) {
            $sour = new SourRef();
            $sour->setSour($item->sour);
            $fam->addSour($sour);
        }

        $_obje = [];
        foreach ($_obje as $item) {
            $obje = new ObjeRef();
            $fam->addObje($obje);
        }
        $this->_gedcom->addFam($fam);

        return $fam;
    }

    protected function setSubn()
    {
    }

    protected function setSubM()
    {
    }

    protected function setSour()
    {
        $sour = new \Gedcom\Record\Sour();
        $_sour = Source::all();
        foreach ($_sour as $item) {
            $sour->setTitl($item->titl);
        }

        $this->_gedcom->addSour($sour);
    }

    protected function setNote()
    {
    }

    protected function setRepo()
    {
    }

    protected function setObje()
    {
    }
}