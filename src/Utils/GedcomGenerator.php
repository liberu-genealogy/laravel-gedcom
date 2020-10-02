<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils;

use GenealogiaWebsite\LaravelGedcom\Models\Family;
use GenealogiaWebsite\LaravelGedcom\Models\Person;
use PhpGedcom\Gedcom;

class GedcomGenerator
{
    protected $family_id;
    protected $p_id;
    protected $up_nest;
    protected $down_nest;
    protected $arr_indi_id = [];
    protected $arr_fam_id = [];
    protected $_gedcom = null;
    protected $log = "\n";

    /**
     * Constructor with family_id.
     */
    public function __construct($p_id = 0, $family_id = 0, $up_nest = 0, $down_nest = 0)
    {
        $this->family_id = $family_id;
        $this->p_id = $p_id;
        $this->up_nest = $up_nest;
        $this->down_nest = $down_nest;
        $this->arr_indi_id = [];
        $this->arr_fam_id = [];
        $this->_gedcom = new Gedcom();
    }

    public function getGedcomFamily()
    {
        $this->setHead();
        $fam = $this->setFam($this->family_id);
        $writer = new \PhpGedcom\Writer();
        $output = $writer->convert($this->_gedcom);

        return $output;
    }

    public function getGedcomPerson()
    {
        $this->setHead();
        $this->addUpData($this->p_id);
        $writer = new \PhpGedcom\Writer();
        $output = $writer->convert($this->_gedcom);

        return $output;
    }

    public function addUpData($p_id, $nest = 0)
    {
        if (empty($p_id) || $p_id < 1) {
            return;
        }
        if ($this->up_nest < $nest) {
            return;
        }

        $person = Person::find($p_id);
        if ($person == null) {
            return;
        }

        // add self to indi
        if (!in_array($p_id, $this->arr_indi_id)) {
            array_push($this->arr_indi_id, $p_id);
            $this->setIndi($p_id);
        } else {
            // already processed this person
            return;
        }

        // process family ( partner, children )
        $_families = Family::where('husband_id', $p_id)->orwhere('wife_id', $p_id)->get();
        foreach ($_families as $item) {
            // add family
            $f_id = $item->id;
            if (!in_array($f_id, $this->arr_fam_id)) {
                array_push($this->arr_fam_id, $f_id);
                $this->setFam($f_id);
            }

            // add partner to indi
            $husb_id = $item->husband_id;
            $wife_id = $item->wife_id;
            $this->log .= $nest.' f_id='.$f_id."\n";
            $this->log .= $nest.' husb_id='.$husb_id."\n";
            $this->log .= $nest.' wife_id='.$wife_id."\n";
            $this->addUpData($husb_id, $nest);
            $this->addUpData($wife_id, $nest);

            // add chidrent to indi
            $children = Person::where('child_in_family_id', $f_id)->get();
            foreach ($children as $item2) {
                $child_id = $item2->id;
                if (!in_array($child_id, $this->arr_indi_id)) {
                    array_push($this->arr_indi_id, $child_id);
                    $this->setIndi($child_id);
                }
            }
        }

        $parent_family_id = $person->child_in_family_id;
        $p_family = Family::find($parent_family_id);

        // there is not parent data.
        if ($p_family === null) {
            return;
        }

        // process siblings
        $siblings = Person::where('child_in_family_id', $parent_family_id)->get();
        foreach ($siblings as $item3) {
            $sibling_id = $item3->id;
            if (!in_array($sibling_id, $this->arr_indi_id)) {
                array_push($this->arr_indi_id, $sibling_id);
                $this->setIndi($sibling_id);
            }
        }

        // process parent
        $nest++;
        $father_id = $p_family->husband_id;
        $mother_id = $p_family->wife_id;
        $this->addUpData($father_id, $nest);
        $this->addUpData($mother_id, $nest);
    }

    public function addDownData($p_id, $nest = 0)
    {
        if (empty($p_id) || $p_id < 1) {
            return;
        }
        if ($this->down_nest < $nest) {
            return;
        }

        $person = Person::find($p_id);
        if ($person == null) {
            return;
        }

        // process self
        if (!in_array($p_id, $this->arr_indi_id)) {
            // add to indi array
            array_push($this->arr_indi_id, $p_id);
            $this->setIndi($p_id);
        }

        $_families = Family::where('husband_id', $p_id)->orwhere('wife_id', $p_id)->get();
        foreach ($_families as $item) {
            // add family
            $f_id = $item->id;
            if (!in_array($f_id, $this->arr_fam_id)) {
                array_push($this->arr_fam_id, $f_id);
                $this->setFam($f_id);
            }
            // process partner
            $husband_id = $item->husband_id;
            $wife_id = $item->wife_id;
            $this->addDownData($husband_id, $nest);
            $this->addDownData($wife_id, $nest);

            // process child
            $children = Person::where('child_in_family_id', $item->id);
            foreach ($children as $item2) {
                $child_id = $item2->id;
                $nest_next = $nest + 1;
                $this->addDownData($child_id, $nest_next);
            }
        }

        // process parent
        $parent_family_id = $person->child_in_family_id;
        $parent_family = Family::find($parent_family_id);
        if ($parent_family != null) {
            $father_id = $parent_family->husband_id;
            $mother_id = $parent_family->wife_id;
            if (!in_array($father_id, $this->arr_indi_id)) {
                array_push($this->arr_indi_id, $father_id);
                $this->setIndi($father_id);
            }
            if (!in_array($mother_id, $this->arr_indi_id)) {
                array_push($this->arr_indi_id, $mother_id);
                $this->setIndi($mother_id);
            }
        }
        // process siblings
        $siblings = Person::where('child_in_family_id', $parent_family_id)->get();
        foreach ($siblings as $item3) {
            $sibling_id = $item3->id;
            if (!in_array($sibling_id, $this->arr_indi_id)) {
                $this->addDownData($sibling_id, $nest);
            }
        }
    }

    protected function setHead()
    {
        $head = new \PhpGedcom\Record\Head();
        /**
         * @var Head\Sour
         */
        $sour = new \PhpGedcom\Record\Head\Sour();
        $sour->setSour(env('APP_NAME', ''));
        $sour->setVersion('1.0');
        $head->setSour($sour);
        /**
         * @var string
         */
        $dest = null;
        $head->setDest($dest);
        /**
         * @var Head\Date
         */
        $date = null;
        $head->setDate($date);
        /**
         * @var string
         */
        $subm = null;
        $head->setSubm($subm);
        /**
         * @var string
         */
        $subn = null;
        $head->setSubn($subn);
        /**
         * @var string
         */
        $file = null;
        $head->setFile($file);
        /**
         * @var string
         */
        $copr = null;
        $head->setCopr($copr);
        /**
         * @var Head\Gedc
         */
        $gedc = null;
        $head->setGedc($gedc);
        /**
         * @var Head\Char
         */
        $char = null;
        $head->setChar($char);
        /**
         * @var string
         */
        $lang = null;
        $head->setLang($lang);
        /**
         * @var Head\Plac
         */
        $plac = null;
        $head->setPlac($plac);
        /**
         * @var string
         */
        $note = null;
        $head->setNote($note);
        $this->_gedcom->setHead($head);
    }

    protected function setIndi($p_id, $f_id = null)
    {
        $indi = new \PhpGedcom\Record\Indi();
        $person = Person::find($p_id);
        if ($person == null) {
            return;
        }
        /**
         * @var string
         */
        $id = $person->id;
        $indi->setId($id);

        /**
         * @var string
         */
        $uid;

        /**
         * @var string
         */
        $chan;

        /**
         * @var Indi\Attr[]
         */
        $attr = [];

        /**
         * @var Indi\Even[]
         */
        $even = [];

        /**
         * @var Indi\Note[]
         */
        $note = [];

        /**
         * @var Obje[]
         */
        $obje = [];

        /**
         * @var Sour[]
         */
        $sour = [];

        /**
         * @var Indi\Name[]
         *                  PhpGedcom\Record\Indi\Name
         */
        $name = [];
        $_name = new \PhpGedcom\Record\Indi\Name();
        $_name->setName($person->name);
        $indi->addName($_name);

        /**
         * @var string[]
         */
        $alia = [];

        /**
         * @var string
         */
        $sex = $person->sex;
        $indi->setSex($sex);

        /**
         * @var string
         */
        $rin;

        /**
         * @var string
         */
        $resn;

        /**
         * @var string
         */
        $rfn;

        /**
         * @var string
         */
        $afn;

        /**
         * @var Indi\Fams[]
         */
        $fams = [];
        $fams = Family::where('husband_id', $p_id)->orwhere('wife_id', $p_id)->get();
        foreach ($fams as $item) {
            $fam = new \PhpGedcom\Record\Indi\Fams();
            $fam->setFams($item->id);
            $indi->addFams($fam);
        }
        /**
         * @var Indi\Famc[]
         */
        $famc = [];

        /**
         * @var Indi\Asso[]
         */
        $asso = [];

        /**
         * @var string[]
         */
        $subm = [];

        /**
         * @var string[]
         */
        $anci = [];

        /**
         * @var string[]
         */
        $desi = [];

        /**
         * @var Refn[]
         */
        $refn = [];

        /**
         * @var Indi\Bapl
         */
        $bapl;

        /**
         * @var Indi\Conl
         */
        $conl;

        /**
         * @var Indi\Endl
         */
        $endl;

        /**
         * @var Indi\Slgc
         */
        $slgc;
        $this->_gedcom->addIndi($indi);
    }

    protected function setFam($family_id)
    {
        $famData = Family::where('id', $family_id)->first();
        if ($famData == null) {
            return;
        }
        $fam = new \PhpGedcom\Record\Fam();
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

        $_chil = Person::where('child_in_family_id', $family_id)->get();
        foreach ($_chil as $item) {
            $fam->addChil($item->id);
            // $this->setIndi($item->id);
        }

        $_even = [];
        foreach ($_even as $item) {
            $even = new \PhpGedcom\Record\Fam\Even();
            $_type = null; // string
            $_date = null; // string
            $_plac = null; // \PhpGedcom\Record\Indi\Even\Plac
            $_caus = null; // string
            $_age = null;  // string
            $_addr = null; // \PhpGedcom\Record\Addr
            $_phon = []; // \PhpGedcom\Record\Phon
            $_agnc = null; // string
            $_husb = null; // \PhpGedcom\Record\Fam\Even\Husb
            $_wife = null; // \PhpGedcom\Record\Fam\Even\Wife
            $_obje = []; // \PhpGedcom\Writer\ObjeRef
            $_sour = []; // \PhpGedcom\Writer\SourRef
            $_note = []; // \PhpGedcom\Writer\NoteRef
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
            $slgs = new \PhpGedcom\Record\Fam\Slgs();
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
            $subm = new \PhpGedcom\Record\Subm();
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
            $refn = new \PhpGedcom\Record\Refn();
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
            $note = new \PhpGedcom\Record\NoteRef();
            $fam->addNote($note);
        }

        $_sour = [];
        foreach ($_sour as $item) {
            $sour = new \PhpGedcom\Record\SourRef();
            $fam->addSour($sour);
        }

        $_obje = [];
        foreach ($_obje as $item) {
            $obje = new \PhpGedcom\Record\ObjeRef();
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
