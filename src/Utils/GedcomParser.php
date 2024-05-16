<?php

namespace FamilyTree365\LaravelGedcom\Utils;

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
use Illuminate\Support\Facades\DB as DB;

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
    /**
     * GedcomParser class is responsible for parsing GEDCOM files and importing the data into the database.
     */
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

        error_log("\n Memory Usage: " . $startMemoryUse . ' MB');
        error_log('PARSE LOG : +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++' . $conn);
        $parser = new Parser();
        $gedcom = @$parser->parse($filename);

        error_log(json_encode($gedcom));
        
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
         * Parses a GEDCOM file and imports the data into the database.
         * 
         * @param mixed $conn Database connection.
         * @param string $filename Path to the GEDCOM file.
         * @param string $slug A unique identifier for the import process.
         * @param bool|null $progressBar Whether to display a progress bar.
         * @param array $channel Information about the progress reporting channel.
         * 
         * This method does not return anything but processes the GEDCOM file.
         */

        $c_subn = 0;
        $c_subm = count($subm);
        $c_sour = count($sour);
        $c_note = count($note);
        $c_repo = count($repo);
        $c_obje = count($obje);
        if ($subn != null) {
            $c_subn = 1;
        }
        $beforeInsert = round(memory_get_usage() / 1_048_576, 2);

        $individuals = $gedcom->getIndi();
        $families = $gedcom->getFam();
        $indCount = count($individuals);
        $famCount = count($families);
        $total = $indCount + $famCount + $c_subn + $c_subm + $c_sour + $c_note + $c_repo + $c_obje;

        
        $progressReporter = new ProgressReporter($total, $channel);
        $mediaParser = new MediaParser($this->conn);
        $mediaParser->parseMediaObjects($obje);
        $progressReporter->advanceProgress($c_obje);

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
            $progressReporter->advanceProgress($c_subm);

            if ($subn != null) {
                // store the submission information for the GEDCOM file.
                // $this->getSubn($subn);
                Subn::read($this->conn, $subn, $this->subm_ids);
                // Removed for brevity
            }
            $progressReporter->advanceProgress($c_subn);

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
            $progressReporter->advanceProgress($c_note);

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
            $progressReporter->advanceProgress($c_repo);

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
            $progressReporter->advanceProgress($c_sour);

            $parentData = ParentData::getPerson($this->conn, $individuals, $this->obje_ids, $this->sour_ids);
           
            $individualParser = new IndividualParser($this->conn);
            $individualParser->parseIndividuals($individuals);
            $progressReporter->advanceProgress(count($individuals));
            

            // complete person-alia and person-asso table with person table
            $alia_list = app(PersonAlia::class)->on($conn)->select('alia')->where('group', 'indi')->where('import_confirm', 0)->get();
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

            $asso_list = app(PersonAsso::class)->on($conn)->select('indi')->where('group', 'indi')->where('import_confirm', 0)->get();
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

            // $familyParser = new FamilyParser($this->conn);
            // $familyParser->parseFamilies($families);     
            FamilyData::getFamily($this->conn, $families, $this->obje_ids, $this->sour_ids, $this->persons_id, $this->note_ids, $this->repo_ids, $parentData);
            $progressReporter->advanceProgress(count($families));

            
        } catch (\Exception $e) {
            $error = $e->getMessage();
            return \Log::error($error);
        }

        $progressReporter->completeProgress();
    }
}
