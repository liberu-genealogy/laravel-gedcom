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

/**
 * GedcomXParser class is responsible for parsing GedcomX files (JSON format) 
 * and importing the data into the database following the same patterns as GedcomParser.
 */
class GedcomXParser
{
    public array $person_ids = [];

    /**
     * Array of persons ID
     * key - old GedcomX ID
     * value - new autoincrement ID.
     *
     * @var array
     */
    protected $persons_id = [];
    protected $subm_ids = [];
    protected $sour_ids = [];
    protected $obje_ids = [];
    protected $note_ids = [];
    protected $repo_ids = [];
    protected $conn = '';

    /**
     * Parse a GedcomX file (JSON format) and import the data into the database.
     *
     * @param mixed $conn Database connection
     * @param string $filename Path to the GedcomX JSON file
     * @param string $slug A unique identifier for the import process
     * @param bool|null $progressBar Whether to display a progress bar
     * @param mixed $tenant Tenant information
     * @param array $channel Information about the progress reporting channel
     * @return void
     */
    public function parse(
        $conn,
        string $filename,
        string $slug,
        bool $progressBar = null,
        $tenant = null,
        $channel = ['name' => 'gedcomx-progress', 'eventName' => 'newMessage']
    ) {
        DB::disableQueryLog();
        $time_start = microtime(true);
        $this->conn = $conn;
        $startMemoryUse = round(memory_get_usage() / 1_048_576, 2);

        error_log("\n Memory Usage: " . $startMemoryUse . ' MB');
        error_log('GEDCOMX PARSE LOG : +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++' . $conn);

        // Read and parse the GedcomX JSON file
        if (!file_exists($filename)) {
            throw new \Exception("GedcomX file not found: " . $filename);
        }

        $jsonContent = file_get_contents($filename);
        if ($jsonContent === false) {
            throw new \Exception("Failed to read GedcomX file: " . $filename);
        }

        $gedcomxData = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in GedcomX file: " . json_last_error_msg());
        }

        error_log("GedcomX file parsed successfully");

        // Initialize arrays for different record types
        $persons = $gedcomxData['persons'] ?? [];
        $relationships = $gedcomxData['relationships'] ?? [];
        $sourceDescriptions = $gedcomxData['sourceDescriptions'] ?? [];
        $agents = $gedcomxData['agents'] ?? [];
        $documents = $gedcomxData['documents'] ?? [];
        $places = $gedcomxData['places'] ?? [];

        // Calculate totals for progress reporting
        $total = count($persons) + count($relationships) + count($sourceDescriptions) + 
                count($agents) + count($documents) + count($places);

        $progressReporter = new ProgressReporter($total, $channel ?? []);

        try {
            // Process agents (similar to submitters in GEDCOM)
            foreach ($agents as $agent) {
                if ($agent) {
                    $agent_id = $agent['id'] ?? null;
                    if ($agent_id) {
                        $subm_id = $this->processAgent($agent);
                        $this->subm_ids[$agent_id] = $subm_id;
                    }
                }
            }
            $progressReporter->advanceProgress(count($agents));

            // Process source descriptions
            foreach ($sourceDescriptions as $sourceDesc) {
                if ($sourceDesc) {
                    $source_id = $sourceDesc['id'] ?? null;
                    if ($source_id) {
                        $sour_id = $this->processSourceDescription($sourceDesc);
                        if ($sour_id != 0) {
                            $this->sour_ids[$source_id] = $sour_id;
                        }
                    }
                }
            }
            $progressReporter->advanceProgress(count($sourceDescriptions));

            // Process documents (media objects)
            foreach ($documents as $document) {
                if ($document) {
                    $doc_id = $document['id'] ?? null;
                    if ($doc_id) {
                        $obje_id = $this->processDocument($document);
                        if ($obje_id != 0) {
                            $this->obje_ids[$doc_id] = $obje_id;
                        }
                    }
                }
            }
            $progressReporter->advanceProgress(count($documents));

            // Process persons
            $parentData = $this->processPersons($persons, $tenant);
            $progressReporter->advanceProgress(count($persons));

            // Process relationships (families)
            $this->processRelationships($relationships, $parentData, $tenant);
            $progressReporter->advanceProgress(count($relationships));

            // Complete person-alia and person-asso tables
            $this->completePersonReferences();

        } catch (\Exception $e) {
            $error = $e->getMessage();
            Log::error("GedcomX parsing error: " . $error);
            throw $e;
        }

        $progressReporter->completeProgress();

        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        error_log("GedcomX parsing completed in: " . $execution_time . " seconds");
    }

    /**
     * Process a GedcomX agent (similar to GEDCOM submitter)
     */
    protected function processAgent(array $agent): int
    {
        // Convert GedcomX agent to GEDCOM-like structure for existing Subm importer
        $gedcomAgent = new \stdClass();
        $gedcomAgent->subm = $agent['id'] ?? '';

        // Map GedcomX agent names to GEDCOM name structure
        if (isset($agent['names']) && is_array($agent['names'])) {
            $name = $agent['names'][0] ?? null;
            if ($name && isset($name['nameForms'])) {
                $nameForm = $name['nameForms'][0] ?? null;
                if ($nameForm && isset($nameForm['fullText'])) {
                    $gedcomAgent->name = $nameForm['fullText'];
                }
            }
        }

        return Subm::read($this->conn, $gedcomAgent, null, null, $this->obje_ids);
    }

    /**
     * Process a GedcomX source description
     */
    protected function processSourceDescription(array $sourceDesc): int
    {
        // Convert GedcomX source description to GEDCOM-like structure
        $gedcomSource = new \stdClass();
        $gedcomSource->sour = $sourceDesc['id'] ?? '';

        // Map titles
        if (isset($sourceDesc['titles']) && is_array($sourceDesc['titles'])) {
            $title = $sourceDesc['titles'][0] ?? null;
            if ($title && isset($title['value'])) {
                $gedcomSource->titl = $title['value'];
            }
        }

        // Map citations
        if (isset($sourceDesc['citations']) && is_array($sourceDesc['citations'])) {
            $citation = $sourceDesc['citations'][0] ?? null;
            if ($citation && isset($citation['value'])) {
                $gedcomSource->text = $citation['value'];
            }
        }

        return Sour::read($this->conn, $gedcomSource, $this->obje_ids);
    }

    /**
     * Process a GedcomX document (media object)
     */
    protected function processDocument(array $document): int
    {
        // Convert GedcomX document to GEDCOM-like structure
        $gedcomObje = new \stdClass();
        $gedcomObje->id = $document['id'] ?? '';

        // Map document text or description
        if (isset($document['text'])) {
            $gedcomObje->titl = substr($document['text'], 0, 100); // Truncate for title
        }

        return Obje::read($this->conn, $gedcomObje);
    }

    /**
     * Process GedcomX persons
     */
    protected function processPersons(array $persons, $tenant): array
    {
        return ParentData::getPersonFromGedcomX($this->conn, $persons, $this->obje_ids, $this->sour_ids, $tenant);
    }

    /**
     * Process GedcomX relationships
     */
    protected function processRelationships(array $relationships, array $parentData, $tenant): void
    {
        FamilyData::getFamilyFromGedcomX(
            $this->conn, 
            $relationships, 
            $this->obje_ids, 
            $this->sour_ids, 
            $this->persons_id, 
            $this->note_ids, 
            $this->repo_ids, 
            $parentData, 
            $tenant
        );
    }

    /**
     * Complete person references (aliases and associations)
     */
    protected function completePersonReferences(): void
    {
        // Complete person-alia table
        $alia_list = app(PersonAlia::class)->on($this->conn)
            ->select('alia')
            ->where('group', 'indi')
            ->where('import_confirm', 0)
            ->get();

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

        // Complete person-asso table
        $asso_list = app(PersonAsso::class)->on($this->conn)
            ->select('indi')
            ->where('group', 'indi')
            ->where('import_confirm', 0)
            ->get();

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
    }

    /**
     * Check if a file is a valid GedcomX file
     */
    public static function isGedcomXFile(string $filename): bool
    {
        if (!file_exists($filename)) {
            return false;
        }

        // Check file extension
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, ['json', 'gedcomx'])) {
            return false;
        }

        // Check if it's valid JSON with GedcomX structure
        $content = file_get_contents($filename);
        if ($content === false) {
            return false;
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        // Check for GedcomX-specific structure
        return isset($data['persons']) || isset($data['relationships']) || 
               isset($data['sourceDescriptions']) || isset($data['agents']);
    }
}