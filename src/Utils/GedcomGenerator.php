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
use \PhpGedcom\Gedcom;
class GedcomGenerator
{
    protected $family_id;
    protected $_gedcom = null;

    public function __construct($family_id){
        $this->family_id = $family_id;
        $this->_gedcom = new Gedcom();
    }

    
    public function getGedcom(){
        $this->setHead();
        $this->setFam($this->family_id);
        $writer = new \PhpGedcom\Writer();
        $output = $writer->convert($this->_gedcom);
        return $output;
    }

    protected function setHead(){
        $head = new \PhpGedcom\Record\Head();
        /**
         * @var Head\Sour
         */
        $sour = null;
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

    protected function setIndi($p_id, $f_id=null){
        $indi = new \PhpGedcom\Record\Indi();
        $person = Person::find($p_id);
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
        $attr = array();

        /**
         * @var Indi\Even[]
         */
        $even = array();

        /**
         * @var Indi\Note[]
         */
        $note = array();

        /**
         * @var Obje[]
         */
        $obje = array();

        /**
         * @var Sour[]
         */
        $sour = array();

        /**
         * @var Indi\Name[]
         * PhpGedcom\Record\Indi\Name
         */
        $name = array();
        $_name = new \PhpGedcom\Record\Indi\Name();
        $_name->setName($person->name);
        $indi->addName($_name);

        /**
         * @var string[]
         */
        $alia = array();

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
        $fams = array();
        $fams = Family::where('husband_id', $p_id)->orwhere('wife_id', $p_id)->get();
        foreach($fams as $item){
            $fam = new \PhpGedcom\Record\Indi\Fams();
            $fam->setFams($item->id);
            $indi->addFams($fam);
        }
        /**
         * @var Indi\Famc[]
         */
        $famc = array();

        /**
         * @var Indi\Asso[]
         */
        $asso = array();

        /**
         * @var string[]
         */
        $subm = array();

        /**
         * @var string[]
         */
        $anci = array();

        /**
         * @var string[]
         */
        $desi = array();

        /**
         * @var Refn[]
         */
        $refn = array();

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

    protected function setFam($family_id){
        $famData = Family::where('id', $family_id)->first();
        
        $fam = new \PhpGedcom\Record\Fam();
        $_id = $famData->id;
        $fam->setId($_id);
        /**
         *
         */
        $_chan = null;
        $fam->setChan($_chan);
        /**
         *
         */
        $_husb = $famData->husband_id;
        $fam->setHusb($_husb);

        // add husb individual
        $this->setIndi($_husb, $family_id);

        /**
         *
         */
        $_wife = $famData->wife_id;
        $fam->setWife($_wife);

        // add wife individual
        $this->setIndi($_wife, $family_id);
    
        /**
         *
         */
        $_nchi = null;
        $fam->setNchi($_nchi);
        /**
         *
         */
        
        $_chil = Person::where('child_in_family_id', $family_id)->get();
        foreach($_chil as $item){
            $fam->addChil($item->id);
            $this->setIndi($item->id);
        }
        
        /**
         *
         */
        $_even = array();
        foreach($_even as $item){
            $even = new \PhpGedcom\Record\Fam\Even();
            $_type = null; // string
            $_date = null; // string 
            $_plac = null; // \PhpGedcom\Record\Indi\Even\Plac
            $_caus = null; // string 
            $_age = null;  // string
            $_addr = null; // \PhpGedcom\Record\Addr
            $_phon = array(); // \PhpGedcom\Record\Phon
            $_agnc = null; // string
            $_husb = null; // \PhpGedcom\Record\Fam\Even\Husb
            $_wife = null; // \PhpGedcom\Record\Fam\Even\Wife
            $_obje = array(); // \PhpGedcom\Writer\ObjeRef
            $_sour = array(); // \PhpGedcom\Writer\SourRef
            $_note = array(); // \PhpGedcom\Writer\NoteRef
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
        /**
         *
         */
        $_slgs = array();
        foreach($_slgs as $item){
            $slgs = new \PhpGedcom\Record\Fam\Slgs();
            $_stat = null;
            $_date = null;
            $_plac = null;
            $_temp = null;
            $_sour = array();
            $_note = array();

            $slgs->setStat($_stat);
            $slgs->setDate($_date);
            $slgs->setPlac($_plac);
            $slgs->setTemp($_temp);
            $slgs->setSour($_sour);
            $slgs->setNote($_note);
            $fam->addSlgs($slgs);
        }
        /**
         *
         */
        $_subm = array();
        foreach($_subm as $item){
            $subm = new \PhpGedcom\Record\Subm();
            $subm_id = null;
            $chan = null; // @var Record\Chan
            $name = null;
            $addr = null; //@var Record\Addr
            $rin = null;
            $rfn = null;
            $lang = array();
            $phon = array();
            $obje = array();
            $note = array();

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
        /**
         *
         */
        $_refn = array();
        foreach($_refn as $item){
            $refn = new \PhpGedcom\Record\Refn();
            $refn = null;
            $type = null;

            $subm->setRefn($refn);
            $subm->setType($type);
            
            $fam->addRefn($refn);
        }
        /**
         *
         */
        $_rin = null;
        $fam->setRin($_rin);
        /**
         *
         */
        $_note = array();
        foreach($_note as $item){
            $note = new \PhpGedcom\Record\NoteRef();
            $fam->addNote($note);
        }
        /**
         *
         */
        $_sour = array();
        foreach($_sour as $item){
            $sour = new \PhpGedcom\Record\SourRef();
            $fam->addSour($sour);
        }
        /**
         *
         */
        $_obje = array();
        foreach($_obje as $item){
            $obje = new \PhpGedcom\Record\ObjeRef();
            $fam->addObje($obje);
        }
    }

    protected function setSubn(){

    }

    protected function setSubM(){

    }

    protected function setSour(){

    }

    protected function setNote(){

    }

    protected function setRepo(){

    }

    protected function setObje(){

    }
}