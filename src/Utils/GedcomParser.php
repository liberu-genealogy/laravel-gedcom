<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils;

use GenealogiaWebsite\LaravelGedcom\Events\GedComProgressSent;
use GenealogiaWebsite\LaravelGedcom\Models\Family;
use GenealogiaWebsite\LaravelGedcom\Models\Person;
use GenealogiaWebsite\LaravelGedcom\Models\PersonAlia;
use GenealogiaWebsite\LaravelGedcom\Models\PersonAsso;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class GedcomParser
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID.
     *
     * @var string
     */
    protected $persons_id = [];
    protected $subm_ids = [];
    protected $sour_ids = [];
    protected $obje_ids = [];
    protected $note_ids = [];
    protected $repo_ids = [];
    protected $conn = '';

    public function parse($conn, string $filename, string $slug, bool $progressBar = false)
    {
        $this->conn = $conn;
        error_log('PARSE LOG : +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++'.$conn);
        $parser = new \PhpGedcom\Parser();
        $gedcom = @$parser->parse($filename);
        // var_dump($gedcom);

        /**
         * work.
         */
        if ($gedcom->getHead())
        {
            $head = $gedcom->getHead();
        }
        if ($gedcom->getSubn())
        {
            $subn = $gedcom->getSubn();
        }
        if ($gedcom->getSubm())
        {
            $subm = $gedcom->getSubm();
        }
        if ($gedcom->getSour())
        {
            $sour = $gedcom->getSour();
        }
        if ($gedcom->getNote())
        {
            $note = $gedcom->getNote();
        }
        if ($gedcom->getRepo())
        {
            $repo = $gedcom->getRepo();
        }
        if ($gedcom->getObje())
        {
            $obje = $gedcom->getObje();
        }



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
        Log::info('Individual:'.count($individuals));
        Log::info('Families:'.count($families));
        Log::info('Subn:'.$c_subn);
        Log::info('Subm:'.$c_subm);
        Log::info('Sour:'.$c_sour);
        Log::info('Note:'.$c_note);
        Log::info('Repo:'.$c_repo);

        // store all the media objects that are contained within the GEDCOM file.
        foreach ($obje as $item) {
            // $this->getObje($item);
            if ($item) {
                $_obje_id = $item->getId();
                $obje_id = \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Obje::read($this->conn, $item);
                if ($obje_id != 0) {
                    $this->obje_ids[$_obje_id] = $obje_id;
                }
            }
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store information about all the submitters to the GEDCOM file.
        foreach ($subm as $item) {
            // $this->getSubm($item);
            if ($item) {
                $_subm_id = $item->getSubm();
                $subm_id = \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Subm::read($this->conn, $item, null, null, $this->obje_ids);
                $this->subm_ids[$_subm_id] = $subm_id;
            }
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        if ($subn != null) {
            // store the submission information for the GEDCOM file.
            // $this->getSubn($subn);
            \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Subn::read($this->conn, $subn, $this->subm_ids);
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store all the notes contained within the GEDCOM file that are not inline.
        foreach ($note as $item) {
            // $this->getNote($item);
            if ($item) {
                $note_id = $item->getId();
                $_note_id = \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Note::read($this->conn, $item);
                $this->note_ids[$note_id] = $_note_id;
            }

            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store all repositories that are contained within the GEDCOM file and referenced by sources.
        foreach ($repo as $item) {
            // $this->getRepo($item);
            if ($item) {
                $repo_id = $item->getRepo();
                $_repo_id = \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Repo::read($this->conn, $item);
                $this->repo_ids[$repo_id] = $_repo_id;
            }
            if ($progressBar === true) {
                $bar->advance();
                $complete++;
                event(new GedComProgressSent($slug, $total, $complete));
            }
        }

        // store sources cited throughout the GEDCOM file.
        // obje import before sour import
        foreach ($sour as $item) {
            // $this->getSour($item);
            if ($item) {
                $_sour_id = $item->getSour();
                $sour_id = \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Sour::read($this->conn, $item, $this->obje_ids);
                if ($sour_id != 0) {
                    $this->sour_ids[$_sour_id] = $sour_id;
                }
            }
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

        // complete person-alia and person-asso table with person table
        $alia_list = PersonAlia::on($conn)->where('group', 'indi')->where('import_confirm', 0)->get();
        foreach ($alia_list as $item) {
            $alia = $item->alia;
            if (isset($this->person_ids[$alia])) {
                $item->alia = $this->person_ids[$alia];
                $item->import_confirm = 1;
                $item->save();
            } else {
                $item->delete();
            }
        }

        $asso_list = PersonAsso::on($conn)->where('group', 'indi')->where('import_confirm', 0)->get();
        foreach ($asso_list as $item) {
            $_indi = $item->indi;
            if (isset($this->person_ids[$_indi])) {
                $item->indi = $this->person_ids[$_indi];
                $item->import_confirm = 1;
                $item->save();
            } else {
                $item->delete();
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

    private function getPerson($individual)
    {
        $g_id = $individual->getId();
        $name = '';
        $givn = '';
        $surn = '';

        $name = '';
        $npfx = '';
        $givn = '';
        $nick = '';
        $spfx = '';
        $surn = '';
        $nsfx = '';
        $type = '';
        $fone = null; // PhpGedcom/
        $romn = null;
        $names = $individual->getName();

        if (!empty($names)) {
            $name = current($names)->getName();
            $npfx = current($names)->getNpfx();
            $givn = current($names)->getGivn();
            $nick = current($names)->getNick();
            $spfx = current($names)->getSpfx();
            $surn = current($names)->getSurn();
            $nsfx = current($names)->getNsfx();
            $type = current($names)->getType();
        }

        // array value
        $fams = $individual->getFams();  // self family, leave it now, note would be included in family
        $famc = $individual->getFamc();  // parent family , leave it now, note and pedi would be included in family

        // added to database
        // string value
        $sex = preg_replace('/[^MF]/', '', $individual->getSex());
        $uid = $individual->getUid();
        $resn = $individual->getResn();
        $rin = $individual->getRin();
        $rfn = $individual->getRfn();
        $afn = $individual->getAfn();

        $attr = $individual->getAllAttr();
        $events = $individual->getAllEven();
        $note = $individual->getNote();
        $indv_sour = $individual->getSour();
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

        $chan = $individual->getChan();

        if ($givn == '') {
            $givn = $name;
        }
        $config = json_encode(config('database.connections.'.$this->conn));
        $person = Person::on($this->conn)->updateOrCreate(compact('name', 'givn', 'surn', 'sex'), compact('name', 'givn', 'surn', 'sex', 'uid', 'rin', 'resn', 'rfn', 'afn'));
        $this->persons_id[$g_id] = $person->id;
        if ($events !== null) {
            foreach ($events as $event) {
                if ($event && count($event) > 0) {
                    $e_data = $event[0];
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Even::read($this->conn, $e_data, $person, $this->obje_ids);
                }
            }
        }

        if ($attr !== null) {
            foreach ($attr as $event) {
                $e_data = $event[0];
                \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Even::read($this->conn, $e_data, $person);
            }
        }

        $_group = 'indi';
        $_gid = $person->id;
        if ($names != null && count($names) > 0) {
            foreach ($names as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Name::read($this->conn, $item, $_group, $_gid);
                }
            }
        }
        if ($note != null && count($note) > 0) {
            foreach ($note as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\NoteRef::read($this->conn, $item, $_group, $_gid);
                }
            }
        }
        if ($indv_sour != null && count($indv_sour) > 0) {
            foreach ($indv_sour as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\SourRef::read($this->conn, $item, $_group, $_gid, $this->sour_ids, $this->obje_ids);
                }
            }
        }

        // ??
        if ($alia && count($alia) > 0) {
            foreach ($alia as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Alia::read($this->conn, $item, $_group, $_gid);
                }
            }
        }
        // ??
        if ($asso && count($asso) > 0) {
            foreach ($asso as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Asso::read($this->conn, $item, $_group, $_gid);
                }
            }
        }

        if ($subm && count($subm) > 0) {
            foreach ($subm as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Subm::read($this->conn, $item, $_group, $_gid, $this->subm_ids);
                }
            }
        }

        if ($anci && count($anci) > 0) {
            foreach ($anci as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Anci::read($this->conn, $item, $_group, $_gid, $this->subm_ids);
                }
            }
        }

        if ($desi && count($desi) > 0) {
            foreach ($desi as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Desi::read($this->conn, $item, $_group, $_gid, $this->subm_ids);
                }
            }
        }

        if ($refn && count($refn) > 0) {
            foreach ($refn as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Refn::read($this->conn, $item, $_group, $_gid);
                }
            }
        }

        if ($obje && count($obje) > 0) {
            foreach ($obje as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\ObjeRef::read($this->conn, $item, $_group, $_gid, $this->obje_ids);
                }
            }
        }

        if ($bapl && count($bapl) > 0) {
            foreach ($bapl as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Lds::read($this->conn, $item, $_group, $_gid, 'BAPL', $this->sour_ids, $this->obje_ids);
                }
            }
        }

        if ($conl && count($conl) > 0) {
            foreach ($conl as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Lds::read($this->conn, $item, $_group, $_gid, 'CONL', $this->sour_ids, $this->obje_ids);
                }
            }
        }

        if ($endl && count($endl) > 0) {
            foreach ($endl as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Lds::read($this->conn, $item, $_group, $_gid, 'ENDL', $this->sour_ids, $this->obje_ids);
                }
            }
        }

        if ($slgc && count($slgc) > 0) {
            foreach ($slgc as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Indi\Lds::read($this->conn, $item, $_group, $_gid, 'SLGC', $this->sour_ids, $this->obje_ids);
                }
            }
        }
        if ($chan) {
            \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Chan::read($this->conn, $chan, $_group, $_gid);
        }
    }

    private function getFamily($family)
    {
        $g_id = $family->getId();
        $resn = $family->getResn();
        $husb = $family->getHusb();
        $wife = $family->getWife();

        // string
        $nchi = $family->getNchi();
        $rin = $family->getRin();
        $type_id = 1;

        // array
        $subm = $family->getSubm();
        $_slgs = $family->getSlgs();

        $description = null;
        $type_id = 0;
        $is_active = 1;

        $children = $family->getChil();
        $events = $family->getAllEven();
        $_note = $family->getNote();
        $_obje = $family->getObje();
        $_sour = $family->getSour();
        $_refn = $family->getRefn();

        // object
        $chan = $family->getChan();

        $husband_id = (isset($this->persons_id[$husb])) ? $this->persons_id[$husb] : 0;
        $wife_id = (isset($this->persons_id[$wife])) ? $this->persons_id[$wife] : 0;

        $family = Family::on($this->conn)->updateOrCreate(
            compact('husband_id', 'wife_id', 'description', 'nchi', 'rin', 'type_id', 'is_active')
        );

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
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Fam\Even::read($this->conn, $item, $family, $this->obje_ids);
                }
                // $date = $this->getDate($item->getDate());
                // $place = $this->getPlace($item->getPlac());
                // $family->addEvent($item->getType(), $date, $place);
            }
        }
        $_group = 'fam';
        $_gid = $family->id;
        if ($_note != null && count($_note) > 0) {
            foreach ($_note as $item) {
                \GenealogiaWebsite\LaravelGedcom\Utils\Importer\NoteRef::read($this->conn, $item, $_group, $_gid);
            }
        }
        if ($_obje && count($_obje) > 0) {
            foreach ($_obje as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\ObjeRef::read($this->conn, $item, $_group, $_gid, $this->obje_ids);
                }
            }
        }
        if ($_refn && count($_refn) > 0) {
            foreach ($_refn as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Refn::read($this->conn, $item, $_group, $_gid);
                }
            }
        }
        if ($_sour && count($_sour) > 0) {
            foreach ($_sour as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\SourRef::read($this->conn, $item, $_group, $_gid, $this->sour_ids, $this->obje_ids);
                }
            }
        }
        if ($_slgs && count($_slgs) > 0) {
            foreach ($_slgs as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Fam\Slgs::read($this->conn, $item, $family);
                }
            }
        }
        if ($subm && count($subm) > 0) {
            foreach ($subm as $item) {
                if ($item) {
                    \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Subm::read($this->conn, $item, $_group, $_gid, $this->obje_ids);
                }
            }
        }
        if ($chan) {
            \GenealogiaWebsite\LaravelGedcom\Utils\Importer\Chan::read($this->conn, $chan, 'family', $family->id);
        }
    }
}