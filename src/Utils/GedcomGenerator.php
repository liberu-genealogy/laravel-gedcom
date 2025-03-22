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

readonly class GedcomGenerator
{
    public function __construct(
        private string $filename,
        private array $options = []
    ) {}

    public function generate(): void
    {
        $gedcom = new Gedcom();

        $this->addHeader($gedcom);
        $this->addSubmitter($gedcom);
        $this->addIndividuals($gedcom);
        $this->addFamilies($gedcom);

        $this->writeGedcom($gedcom);
    }

    private function addHeader(Gedcom $gedcom): void
    {
        $header = new Head();
        $sour = new Sour();
        $sour->setSour(env('APP_NAME', ''));
        $header->setSour($sour);
        $header->setGedc(['VERS' => '5.5.5', 'FORM' => 'LINEAGE-LINKED']);
        $header->setChar('UTF-8');
        $gedcom->setHead($header);
    }

    private function addSubmitter(Gedcom $gedcom): void
    {
        $sour = new Sour();
        $sour->setSour(env('APP_NAME', ''));
        $sour->setVersion('1.0');
        $subm = new Subm();
        $subm->setSour($sour);
        $gedcom->addSubm($subm);
    }

    private function addIndividuals(Gedcom $gedcom): void
    {
        $persons = app(Person::class)->query()->get();
        if ($persons == null) {
            return;
        }
        foreach ($persons as $person) {
            $this->setIndi($gedcom, $person->id);
        }
    }

    private function setIndi(Gedcom $gedcom, $p_id): void
    {
        $person = app(Person::class)->query()->find($p_id);
        if ($person == null) {
            return;
        }
        $indi = new Indi();
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

        $gedcom->addIndi($indi);
    }

    private function addFamilies(Gedcom $gedcom): void
    {
        $_families = app(Family::class)->all();
        foreach ($_families as $item) {
            $this->setFam($gedcom, $item->id);
        }
    }

    private function setFam(Gedcom $gedcom, $family_id): void
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
        $gedcom->addFam($fam);

        return $fam;
    }

    private function setSubn(): void
    {
    }

    private function setSubM(): void
    {
    }

    private function setSour(): void
    {
        $sour = new \Gedcom\Record\Sour();
        $_sour = Source::all();
        foreach ($_sour as $item) {
            $sour->setTitl($item->titl);
        }

        $gedcom->addSour($sour);
    }

    private function setNote(): void
    {
    }

    private function setRepo(): void
    {
    }

    private function setObje(): void
    {
    }

    private function writeGedcom(Gedcom $gedcom): void
    {
        $writer = new Writer();
        $writer->write($gedcom, $this->filename);
    }
}