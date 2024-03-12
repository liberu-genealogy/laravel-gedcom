<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use DB;
use FamilyTree365\LaravelGedcom\Events\GedComProgressSent;
use FamilyTree365\LaravelGedcom\Models\PersonAlia;
use FamilyTree365\LaravelGedcom\Models\PersonAsso;
use FamilyTree365\LaravelGedcom\Utils\Importer\Note;
use FamilyTree365\LaravelGedcom\Utils\Importer\Obje;
use FamilyTree365\LaravelGedcom\Utils\Importer\Repo;
use FamilyTree365\LaravelGedcom\Utils\Importer\Sour;
use FamilyTree365\LaravelGedcom\Utils\Importer\Subm;
use FamilyTree365\LaravelGedcom\Utils\Importer\Subn;
use Gedcom\Parser;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\StringInput;
use FamilyTree365\LaravelGedcom\Utils\Importer\IndividualParser;
use FamilyTree365\LaravelGedcom\Utils\Importer\FamilyParser;
use FamilyTree365\LaravelGedcom\Utils\Importer\MediaParser;
use FamilyTree365\LaravelGedcom\Utils\ProgressReporter;

class GedcomParser
{
    public $person_ids;
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

    public function parse(
        $conn,
        string $filename,
        string $slug,
        bool $progressBar = null,
        $channel = ['name' => 'gedcom-progress1', 'eventName' => 'newMessage']
    ) {
        DB::disableQueryLog();
        //start calculating the time
        $time_start = microtime(true);
        $this->conn = $conn;
        //start calculating the memory - https://www.php.net/manual/en/function.memory-get-usage.php
        $startMemoryUse = round(memory_get_usage() / 1_048_576, 2);

        error_log("\n Memory Usage: ".$startMemoryUse.' MB');
        error_log('PARSE LOG : +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++'.$conn);
        $parser = new Parser();
        $gedcom = @$parser->parse($filename);

        /**
         * work.
         */
        $subn = [];
        $subm = [];
        $sour = [];
        $note = [];
        $repo = [];
        $obje = [];

        if ($gedcom->getSubn()) {
            $subn = $gedcom->getSubn();
        }
        if ($gedcom->getSubm()) {
            $subm = $gedcom->getSubm();
        }
        if ($gedcom->getSour()) {
            $sour = $gedcom->getSour();
        }
        if ($gedcom->getNote()) {
            $note = $gedcom->getNote();
        }
        if ($gedcom->getRepo()) {
            $repo = $gedcom->getRepo();
        }
        if ($gedcom->getObje()) {
            $obje = $gedcom->getObje();
        }
        /**
         * work end.
         */
        $c_subn = 0;
        $c_subm = count($subm);
        $progressReporter = new ProgressReporter($total, $channel);
        $mediaParser = new MediaParser($this->conn);
        $mediaParser->parseMediaObjects($obje);
        $progressReporter->advanceProgress(count($obje));

        try {
            // store all the media objects that are contained within the GEDCOM file.
            foreach ($obje as $item) {
                // $this->getObje($item);
                if ($item) {
                    $_obje_id = $item->getId();
                    $obje_id = Obje::read($this->conn, $item);
                    if ($obje_id != 0) {
                        $this->obje_ids[$_obje_id] = $obje_id;
                    }
                }
                if ($progressBar === true) {
                    $bar->advance();
                    $complete++;
                    event(new GedComProgressSent($slug, $total, $complete, $channel));
                }
            }
            // store information about all the submitters to the GEDCOM file.
            foreach ($subm as $item) {
                if ($item) {
                    $_subm_id = $item->getSubm();
                    $subm_id = Subm::read($this->conn, $item, null, null, $this->obje_ids);
                    $this->subm_ids[$_subm_id] = $subm_id;
                }
                // Removed for brevity
            }

            if ($subn != null) {
                // store the submission information for the GEDCOM file.
                // $this->getSubn($subn);
                Subn::read($this->conn, $subn, $this->subm_ids);
                // Removed for brevity
            }

            // store all the notes contained within the GEDCOM file that are not inline.
            foreach ($note as $item) {
                // $this->getNote($item);
                if ($item) {
                    $note_id = $item->getId();
                    $_note_id = Note::read($this->conn, $item);
                    $this->note_ids[$note_id] = $_note_id;
                }
                // Removed for brevity
            }

            // store all repositories that are contained within the GEDCOM file and referenced by sources.
            foreach ($repo as $item) {
                // $this->getRepo($item);
                if ($item) {
                    $repo_id = $item->getRepo();
                    $_repo_id = Repo::read($this->conn, $item);
                    $this->repo_ids[$repo_id] = $_repo_id;
                }
                // Removed for brevity
            }

            // store sources cited throughout the GEDCOM file.
            // obje import before sour import
            foreach ($sour as $item) {
                // $this->getSour($item);
                if ($item) {
                    $_sour_id = $item->getSour();
                    $sour_id = Sour::read($this->conn, $item, $this->obje_ids);
                    if ($sour_id != 0) {
                        $this->sour_ids[$_sour_id] = $sour_id;
                    }
                }
                // Removed for brevity
            }

            $parentData = ParentData::getPerson($this->conn, $individuals, $this->obje_ids, $this->sour_ids);

            foreach ($individuals as $individual) {
                $individualParser = new IndividualParser($this->conn);
                $individualParser->parseIndividuals($individuals);
                $progressReporter->advanceProgress(count($individuals));
            }

            // complete person-alia and person-asso table with person table
            $alia_list = PersonAlia::on($conn)->select('alia')->where('group', 'indi')->where('import_confirm', 0)->get();
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

            $asso_list = PersonAsso::on($conn)->select('indi')->where('group', 'indi')->where('import_confirm', 0)->get();
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

            $familyParser = new FamilyParser($this->conn);
            $familyParser->parseFamilies($families);
            $progressReporter->advanceProgress(count($families));

            foreach ($families as $family) {
                FamilyData::getFamily($this->conn, $family, $this->obje_ids);
                // Removed for brevity
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();

            return \Log::error($error);
        }

        $progressReporter->completeProgress();
    }

    private function getProgressBar(int $max)
    {
        return (new OutputStyle(
            new StringInput(''),
            new StreamOutput(fopen('php://stdout', 'w'))
        ))->createProgressBar($max);
    }
}
