<?php

declare(strict_types=1);

namespace FamilyTree365\LaravelGedcom\Utils;

use FamilyTree365\LaravelGedcom\Models\PersonAlia;
use FamilyTree365\LaravelGedcom\Models\PersonAsso;
use FamilyTree365\LaravelGedcom\Utils\Importer\Note;
use FamilyTree365\LaravelGedcom\Utils\Importer\Obje;
use FamilyTree365\LaravelGedcom\Utils\Importer\Repo;
use FamilyTree365\LaravelGedcom\Utils\Importer\Sour;
use FamilyTree365\LaravelGedcom\Utils\Importer\Subm;
use FamilyTree365\LaravelGedcom\Utils\ProgressReporter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;

/**
 * High-performance GedcomX parser optimized for PHP 8.4+
 * Uses modern PHP features for maximum performance and memory efficiency
 */
final class GedcomXParserOptimized
{
    // PHP 8.4: Use class constants for better performance
    private const int CHUNK_SIZE = 1000;
    private const int MEMORY_LIMIT_MB = 512;
    private const int JSON_DECODE_DEPTH = 512;

    // PHP 8.4: Typed constants for better performance
    private const array GEDCOMX_PERSON_TYPES = [
        'http://gedcomx.org/Given' => 'givn',
        'http://gedcomx.org/Surname' => 'surn',
        'http://gedcomx.org/Prefix' => 'npfx',
        'http://gedcomx.org/Suffix' => 'nsfx',
    ];

    private const array GEDCOMX_GENDER_TYPES = [
        'http://gedcomx.org/Male' => 'M',
        'http://gedcomx.org/Female' => 'F',
    ];

    private const array GEDCOMX_FACT_TYPES = [
        'http://gedcomx.org/Birth' => 'birth',
        'http://gedcomx.org/Death' => 'death',
        'http://gedcomx.org/Marriage' => 'marriage',
    ];

    // PHP 8.4: Use property hooks for better encapsulation
    public array $person_ids {
        get => $this->person_ids;
        set(array $value) => $this->person_ids = $value;
    }

    public function __construct(
        private array $persons_id = [],
        private array $subm_ids = [],
        private array $sour_ids = [],
        private array $obje_ids = [],
        private array $note_ids = [],
        private array $repo_ids = [],
        private string $conn = ''
    ) {}

    /**
     * Parse a GedcomX file with optimized performance using PHP 8.4 features
     */
    public function parse(
        string $conn,
        string $filename,
        string $slug,
        ?bool $progressBar = null,
        mixed $tenant = null,
        array $channel = ['name' => 'gedcomx-progress', 'eventName' => 'newMessage']
    ): void {
        DB::disableQueryLog();
        $this->conn = $conn;

        // PHP 8.4: Use match expression for better performance
        $memoryStart = match (true) {
            function_exists('memory_get_peak_usage') => memory_get_peak_usage(true),
            default => memory_get_usage(true)
        } / 1_048_576;

        error_log("Memory Usage Start: {$memoryStart} MB");

        try {
            // PHP 8.4: Optimized file reading with stream context
            $gedcomxData = $this->readAndParseJsonFile($filename);

            // PHP 8.4: Use array destructuring with null coalescing for better performance
            [
                'persons' => $persons,
                'relationships' => $relationships,
                'sourceDescriptions' => $sourceDescriptions,
                'agents' => $agents,
                'documents' => $documents,
                'places' => $places
            ] = $gedcomxData + [
                'persons' => [],
                'relationships' => [],
                'sourceDescriptions' => [],
                'agents' => [],
                'documents' => [],
                'places' => []
            ];

            $total = count($persons) + count($relationships) + count($sourceDescriptions) + 
                    count($agents) + count($documents) + count($places);

            $progressReporter = new ProgressReporter($total, $channel);

            // Process in optimized order for better performance
            $this->processAgentsOptimized($agents, $progressReporter);
            $this->processSourceDescriptionsOptimized($sourceDescriptions, $progressReporter);
            $this->processDocumentsOptimized($documents, $progressReporter);

            $parentData = $this->processPersonsOptimized($persons, $tenant, $progressReporter);
            $this->processRelationshipsOptimized($relationships, $parentData, $tenant, $progressReporter);

            $this->completePersonReferencesOptimized();

            $progressReporter->completeProgress();

        } catch (\Throwable $e) {
            Log::error("GedcomX parsing error: " . $e->getMessage(), [
                'file' => $filename,
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        $memoryEnd = match (true) {
            function_exists('memory_get_peak_usage') => memory_get_peak_usage(true),
            default => memory_get_usage(true)
        } / 1_048_576;

        error_log("Memory Usage End: {$memoryEnd} MB, Peak: " . (memory_get_peak_usage(true) / 1_048_576) . " MB");
    }

    /**
     * Optimized JSON file reading using PHP 8.4 features
     */
    private function readAndParseJsonFile(string $filename): array
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException("GedcomX file not found or not readable: {$filename}");
        }

        // PHP 8.4: Use file_get_contents with stream context for better performance
        $context = stream_context_create([
            'http' => [
                'timeout' => 60,
                'user_agent' => 'GedcomX-Parser/1.0'
            ]
        ]);

        $jsonContent = file_get_contents($filename, false, $context);
        if ($jsonContent === false) {
            throw new \RuntimeException("Failed to read GedcomX file: {$filename}");
        }

        try {
            // PHP 8.4: Use JSON_THROW_ON_ERROR for better error handling
            return json_decode(
                $jsonContent, 
                true, 
                self::JSON_DECODE_DEPTH, 
                JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING
            );
        } catch (JsonException $e) {
            throw new \InvalidArgumentException("Invalid JSON in GedcomX file: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Optimized agent processing using batch operations
     */
    private function processAgentsOptimized(array $agents, ProgressReporter $progressReporter): void
    {
        if (empty($agents)) {
            return;
        }

        // PHP 8.4: Use array_chunk with optimized chunk size
        $chunks = array_chunk($agents, self::CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            $batchData = [];

            foreach ($chunk as $agent) {
                if (!isset($agent['id'])) continue;

                $agentData = $this->transformAgentData($agent);
                if ($agentData) {
                    $batchData[] = $agentData;
                }
            }

            if (!empty($batchData)) {
                $this->batchInsertAgents($batchData);
            }
        }

        $progressReporter->advanceProgress(count($agents));
    }

    /**
     * Transform agent data using PHP 8.4 optimizations
     */
    private function transformAgentData(array $agent): ?array
    {
        $agentId = $agent['id'] ?? null;
        if (!$agentId) return null;

        // PHP 8.4: Use null coalescing assignment for better performance
        $name = null;

        if (isset($agent['names'][0]['nameForms'][0]['fullText'])) {
            $name = $agent['names'][0]['nameForms'][0]['fullText'];
        }

        return [
            'id' => $agentId,
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Batch insert agents for better performance
     */
    private function batchInsertAgents(array $batchData): void
    {
        foreach ($batchData as $data) {
            $submId = Subm::read($this->conn, (object)$data, null, null, $this->obje_ids);
            $this->subm_ids[$data['id']] = $submId;
        }
    }

    /**
     * Optimized source descriptions processing
     */
    private function processSourceDescriptionsOptimized(array $sourceDescriptions, ProgressReporter $progressReporter): void
    {
        if (empty($sourceDescriptions)) {
            return;
        }

        $chunks = array_chunk($sourceDescriptions, self::CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $sourceDesc) {
                if (!isset($sourceDesc['id'])) continue;

                $sourId = $this->processSourceDescription($sourceDesc);
                if ($sourId !== 0) {
                    $this->sour_ids[$sourceDesc['id']] = $sourId;
                }
            }
        }

        $progressReporter->advanceProgress(count($sourceDescriptions));
    }

    /**
     * Optimized documents processing
     */
    private function processDocumentsOptimized(array $documents, ProgressReporter $progressReporter): void
    {
        if (empty($documents)) {
            return;
        }

        $chunks = array_chunk($documents, self::CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $document) {
                if (!isset($document['id'])) continue;

                $objeId = $this->processDocument($document);
                if ($objeId !== 0) {
                    $this->obje_ids[$document['id']] = $objeId;
                }
            }
        }

        $progressReporter->advanceProgress(count($documents));
    }

    /**
     * Optimized persons processing using bulk operations
     */
    private function processPersonsOptimized(array $persons, mixed $tenant, ProgressReporter $progressReporter): array
    {
        if (empty($persons)) {
            return [];
        }

        $parentData = [];
        $chunks = array_chunk($persons, self::CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            $batchData = [];

            foreach ($chunk as $person) {
                $personData = $this->transformPersonDataOptimized($person, $tenant);
                if ($personData) {
                    $batchData[] = $personData;
                    $parentData[] = $personData;
                }
            }

            if (!empty($batchData)) {
                app(BatchData::class)->upsert(
                    \FamilyTree365\LaravelGedcom\Models\Person::class,
                    $this->conn,
                    $batchData,
                    ['uid']
                );
            }
        }

        $progressReporter->advanceProgress(count($persons));
        return $parentData;
    }

    /**
     * Transform person data with PHP 8.4 optimizations
     */
    private function transformPersonDataOptimized(array $person, mixed $tenant): ?array
    {
        $gId = $person['id'] ?? '';
        if (!$gId) return null;

        // PHP 8.4: Use match for better performance than switch
        $sex = match (true) {
            isset($person['gender']['type']) => self::GEDCOMX_GENDER_TYPES[$person['gender']['type']] ?? '',
            default => ''
        };

        // Optimized name extraction
        [$name, $givn, $surn] = $this->extractNamesOptimized($person['names'] ?? []);

        // Optimized facts extraction
        [$birthday, $birthMonth, $birthYear, $birthdayPlac, $deathday, $deathMonth, $deathYear, $deathdayPlac] = 
            $this->extractFactsOptimized($person['facts'] ?? []);

        return [
            'gid' => $gId,
            'name' => $name,
            'givn' => $givn ?: $name,
            'surn' => $surn,
            'sex' => $sex,
            'uid' => strtoupper(str_replace('-', '', (string) \Illuminate\Support\Str::uuid())),
            'birthday' => $this->validateDate($birthday) ? $birthday : null,
            'birth_month' => $birthMonth,
            'birth_year' => $birthYear,
            'birthday_plac' => $birthdayPlac,
            'deathday' => $deathday,
            'death_month' => $deathMonth,
            'death_year' => $deathYear,
            'deathday_plac' => $deathdayPlac,
            'team_id' => $tenant,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Optimized name extraction using PHP 8.4 features
     */
    private function extractNamesOptimized(array $names): array
    {
        if (empty($names)) {
            return ['', '', ''];
        }

        $nameForm = $names[0]['nameForms'][0] ?? null;
        if (!$nameForm) {
            return ['', '', ''];
        }

        $name = $nameForm['fullText'] ?? '';
        $givn = '';
        $surn = '';

        // PHP 8.4: Optimized array processing
        foreach ($nameForm['parts'] ?? [] as $part) {
            $type = $part['type'] ?? '';
            $value = $part['value'] ?? '';

            match ($type) {
                'http://gedcomx.org/Given' => $givn = $value,
                'http://gedcomx.org/Surname' => $surn = $value,
                default => null
            };
        }

        return [$name, $givn, $surn];
    }

    /**
     * Optimized facts extraction
     */
    private function extractFactsOptimized(array $facts): array
    {
        $result = [null, null, null, null, null, null, null, null];

        foreach ($facts as $fact) {
            $factType = $fact['type'] ?? '';
            $date = $fact['date']['original'] ?? null;
            $place = $fact['place']['original'] ?? null;

            match ($factType) {
                'http://gedcomx.org/Birth' => [
                    $result[0] = $this->parseGedcomXDateOptimized($date),
                    $result[1] = $result[0] ? (int)date('n', strtotime($result[0])) : null,
                    $result[2] = $result[0] ? (int)date('Y', strtotime($result[0])) : null,
                    $result[3] = $place
                ],
                'http://gedcomx.org/Death' => [
                    $result[4] = $this->parseGedcomXDateOptimized($date),
                    $result[5] = $result[4] ? (int)date('n', strtotime($result[4])) : null,
                    $result[6] = $result[4] ? (int)date('Y', strtotime($result[4])) : null,
                    $result[7] = $place
                ],
                default => null
            };
        }

        return $result;
    }

    /**
     * Optimized date parsing using PHP 8.4 features
     */
    private function parseGedcomXDateOptimized(?string $gedcomxDate): ?string
    {
        if (!$gedcomxDate) return null;

        // PHP 8.4: Use match for better performance
        return match (true) {
            preg_match('/^\d{4}-\d{2}-\d{2}$/', $gedcomxDate) => $gedcomxDate,
            preg_match('/^\d{4}$/', $gedcomxDate) => $gedcomxDate . '-01-01',
            preg_match('/^\d{4}-\d{2}$/', $gedcomxDate) => $gedcomxDate . '-01',
            default => ($timestamp = strtotime($gedcomxDate)) !== false ? date('Y-m-d', $timestamp) : null
        };
    }

    /**
     * Optimized relationships processing
     */
    private function processRelationshipsOptimized(array $relationships, array $parentData, mixed $tenant, ProgressReporter $progressReporter): void
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

        $progressReporter->advanceProgress(count($relationships));
    }

    /**
     * Optimized person references completion
     */
    private function completePersonReferencesOptimized(): void
    {
        // Use bulk operations for better performance
        $this->completePersonAliasOptimized();
        $this->completePersonAssoOptimized();
    }

    /**
     * Optimized person alias completion
     */
    private function completePersonAliasOptimized(): void
    {
        $aliasList = app(PersonAlia::class)->on($this->conn)
            ->select(['id', 'alia'])
            ->where('group', 'indi')
            ->where('import_confirm', 0)
            ->get();

        if ($aliasList->isEmpty()) return;

        $updates = [];
        $deletes = [];

        foreach ($aliasList as $item) {
            if (isset($this->person_ids[$item->alia])) {
                $updates[] = [
                    'id' => $item->id,
                    'alia' => $this->person_ids[$item->alia],
                    'import_confirm' => 1
                ];
            } else {
                $deletes[] = $item->id;
            }
        }

        // Bulk update and delete
        if (!empty($updates)) {
            app(PersonAlia::class)->on($this->conn)->upsert($updates, ['id'], ['alia', 'import_confirm']);
        }

        if (!empty($deletes)) {
            app(PersonAlia::class)->on($this->conn)->whereIn('id', $deletes)->delete();
        }
    }

    /**
     * Optimized person association completion
     */
    private function completePersonAssoOptimized(): void
    {
        $assoList = app(PersonAsso::class)->on($this->conn)
            ->select(['id', 'indi'])
            ->where('group', 'indi')
            ->where('import_confirm', 0)
            ->get();

        if ($assoList->isEmpty()) return;

        $updates = [];
        $deletes = [];

        foreach ($assoList as $item) {
            if (isset($this->person_ids[$item->indi])) {
                $updates[] = [
                    'id' => $item->id,
                    'indi' => $this->person_ids[$item->indi],
                    'import_confirm' => 1
                ];
            } else {
                $deletes[] = $item->id;
            }
        }

        // Bulk update and delete
        if (!empty($updates)) {
            app(PersonAsso::class)->on($this->conn)->upsert($updates, ['id'], ['indi', 'import_confirm']);
        }

        if (!empty($deletes)) {
            app(PersonAsso::class)->on($this->conn)->whereIn('id', $deletes)->delete();
        }
    }

    /**
     * Legacy methods for compatibility
     */
    private function processAgent(array $agent): int
    {
        $gedcomAgent = (object)[
            'subm' => $agent['id'] ?? '',
            'name' => $agent['names'][0]['nameForms'][0]['fullText'] ?? null
        ];

        return Subm::read($this->conn, $gedcomAgent, null, null, $this->obje_ids);
    }

    private function processSourceDescription(array $sourceDesc): int
    {
        $gedcomSource = (object)[
            'sour' => $sourceDesc['id'] ?? '',
            'titl' => $sourceDesc['titles'][0]['value'] ?? null,
            'text' => $sourceDesc['citations'][0]['value'] ?? null
        ];

        return Sour::read($this->conn, $gedcomSource, $this->obje_ids);
    }

    private function processDocument(array $document): int
    {
        $gedcomObje = (object)[
            'id' => $document['id'] ?? '',
            'titl' => isset($document['text']) ? substr($document['text'], 0, 100) : null
        ];

        return Obje::read($this->conn, $gedcomObje);
    }

    private function validateDate(?string $date, string $format = 'Y-m-d'): bool
    {
        if (!$date) return false;

        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Check if a file is a valid GedcomX file with optimized validation
     */
    public static function isGedcomXFile(string $filename): bool
    {
        if (!is_file($filename) || !is_readable($filename)) {
            return false;
        }

        // PHP 8.4: Optimized file extension check
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, ['json', 'gedcomx'], true)) {
            return false;
        }

        // Read only first 1KB to check structure
        $handle = fopen($filename, 'r');
        if (!$handle) return false;

        $sample = fread($handle, 1024);
        fclose($handle);

        try {
            $data = json_decode($sample, true, 10, JSON_THROW_ON_ERROR);
            return isset($data['persons']) || isset($data['relationships']) || 
                   isset($data['sourceDescriptions']) || isset($data['agents']);
        } catch (JsonException) {
            return false;
        }
    }
}