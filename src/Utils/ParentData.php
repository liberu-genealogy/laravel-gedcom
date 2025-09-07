<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Models\Person;
use Illuminate\Support\Str;

class ParentData
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

    public static function getPerson($conn, $individuals, $obje_ids = [], $sour_ids = [], $tenant = null)
    {
        $ParentData = [];
        $a = [];

        try {
            foreach ($individuals as $individual) {
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
                $fone = null; // Gedcom/
                $romn = null;
                $names = $individual->getName();
                $attr = $individual->getAllAttr();
                $events = $individual->getAllEven();
                $note = $individual->getNote();
                $indv_sour = $individual->getSour();
                $alia = $individual->getAlia(); // string array
                $asso = $individual->getAsso();
                $subm = $individual->getSubm();
                $anci = $individual->getAnci();
                $refn = $individual->getRefn();
                $obje = $individual->getObje();
                // object
                $bapl = $individual->getBapl();
                $conl = $individual->getConl();
                $endl = $individual->getEndl();
                $slgc = $individual->getSlgc();
                $chan = $individual->getChan();
                $g_id = $individual->getId();

                if (!empty($names)) {
                    $name = current($names)->getName() ?: '';
                    $npfx = current($names)->getNpfx() ?: '';
                    $givn = current($names)->getGivn() ?: '';
                    $nick = current($names)->getNick() ?: '';
                    $spfx = current($names)->getSpfx() ?: '';
                    $surn = current($names)->getSurn() ?: '';
                    $nsfx = current($names)->getNsfx() ?: '';
                    $type = current($names)->getType() ?: '';
                }

                // array value
                $fams = $individual->getFams();  // self family, leave it now, note would be included in family
                $famc = $individual->getFamc();  // parent family , leave it now, note and pedi would be included in family

                // added to database
                // string value
                $sex = preg_replace('/[^MF]/', '', (string) $individual->getSex());
                $uid = $individual->getUid() ?? strtoupper(str_replace('-', '', (string) Str::uuid()));
                $resn = $individual->getResn();
                $rin = $individual->getRin();
                $rfn = $individual->getRfn();
                $afn = $individual->getAfn();
                $titl = $individual->getAttr();

                $birt = $individual->getBirt();
                $birthday = $birt->dateFormatted ?? null;

                $birth_month = $birt->month ?? null;
                $birth_year = $birt->year ?? null;
                $birthday_dati = $birt->dati ?? null;
                $birthday_plac = $birt->plac ?? null;

                $deat = $individual->getDeat();
                $deathday = $deat->dateFormatted ?? null;
                $death_month = $deat->month ?? null;
                $death_year = $deat->year ?? null;
                $deathday_dati = $deat->dati ?? null;
                $deathday_plac = $deat->plac ?? null;
                $deathday_caus = $deat->caus ?? null;

                $buri = $individual->getBuri();
                $burial_day = $buri->dateFormatted ?? null;
                $burial_month = $buri->month ?? null;
                $burial_year = $buri->year ?? null;
                $burial_day_dati = $buri->dati ?? null;
                $burial_day_plac = $buri->plac ?? null;

                $chr = $individual->getChr();
                $chr = $chr->dateFormatted ?? null;

                if ($givn == '') {
                    $givn = $name;
                }

                $config = json_encode(config('database.connections.'.$conn), JSON_THROW_ON_ERROR);
                $value = [
                    'gid'             => $g_id,
                    'name'            => $name,
                    'givn'            => $givn,
                    'surn'            => $surn,
                    'sex'             => $sex,
                    'uid'             => $uid,
                    'rin'             => $rin,
                    'resn'            => $resn,
                    'rfn'             => $rfn,
                    'afn'             => $afn,
                    'nick'            => $nick,
                    'type'            => $type,
                    'chan'            => $chan ? $chan->getDatetime() : null,
                    'nsfx'            => $nsfx,
                    'npfx'            => $npfx,
                    'spfx'            => $spfx,
                    'birthday'        => self::validateDate($birthday) ? $birthday : null,
                    'birth_month'     => $birth_month,
                    'birth_year'      => $birth_year,
                    'birthday_dati'   => mb_convert_encoding((string) $birthday_dati, 'UTF-8', 'ISO-8859-1'),
                    'birthday_plac'   => mb_convert_encoding((string) $birthday_plac, 'UTF-8', 'ISO-8859-1'),
                    'deathday'        => $deathday,
                    'death_month'     => $death_month,
                    'death_year'      => $death_year,
                    'deathday_dati'   => $deathday_dati,
                    'deathday_plac'   => mb_convert_encoding((string) $deathday_plac, 'UTF-8', 'ISO-8859-1'),
                    'deathday_caus'   => $deathday_caus,
                    'burial_day'      => $burial_day,
                    'burial_month'    => $burial_month,
                    'burial_year'     => $burial_year,
                    'burial_day_dati' => $burial_day_dati,
                    'burial_day_plac' => $burial_day_plac,
                    'titl'            => array_key_exists('TITL', $attr) ? $attr['TITL'][0]->getAttr('TITL') : null,
                    'famc'            => $famc ? $famc[0]->getFamc() : null,
                    'fams'            => $fams ? $fams[0]->getFams() : null,
                    'chr'             => $chr,
                    'team_id'         => $tenant,
                ];

                $parentData[] = $value;
            }

            $chunk = array_chunk($parentData, 500);

            foreach ($chunk as $item) {
                // it's take only 1 second for 3010 record
                $a = app(BatchData::class)->upsert(Person::class, $conn, $item, ['uid']);
            }

            otherFields::insertOtherFields($conn, $individuals, $obje_ids, $sour_ids);

            return $parentData;
        } catch (\Exception $e) {
            return \Log::error($e);
        }
    }

    /**
     * Process GedcomX persons data and convert to Laravel models
     *
     * @param mixed $conn Database connection
     * @param array $persons Array of GedcomX person objects
     * @param array $obje_ids Media object IDs mapping
     * @param array $sour_ids Source IDs mapping
     * @param mixed $tenant Tenant information
     * @return array Processed parent data
     */
    public static function getPersonFromGedcomX($conn, $persons, $obje_ids = [], $sour_ids = [], $tenant = null)
    {
        $parentData = [];

        try {
            foreach ($persons as $person) {
                $g_id = $person['id'] ?? '';
                $name = '';
                $givn = '';
                $surn = '';
                $sex = '';
                $uid = strtoupper(str_replace('-', '', (string) \Illuminate\Support\Str::uuid()));

                // Extract names from GedcomX format
                if (isset($person['names']) && is_array($person['names'])) {
                    $nameObj = $person['names'][0] ?? null;
                    if ($nameObj && isset($nameObj['nameForms'])) {
                        $nameForm = $nameObj['nameForms'][0] ?? null;
                        if ($nameForm) {
                            $name = $nameForm['fullText'] ?? '';

                            // Extract name parts
                            if (isset($nameForm['parts'])) {
                                foreach ($nameForm['parts'] as $part) {
                                    $type = $part['type'] ?? '';
                                    $value = $part['value'] ?? '';

                                    switch ($type) {
                                        case 'http://gedcomx.org/Given':
                                            $givn = $value;
                                            break;
                                        case 'http://gedcomx.org/Surname':
                                            $surn = $value;
                                            break;
                                    }
                                }
                            }
                        }
                    }
                }

                // Extract gender from GedcomX format
                if (isset($person['gender'])) {
                    $genderType = $person['gender']['type'] ?? '';
                    switch ($genderType) {
                        case 'http://gedcomx.org/Male':
                            $sex = 'M';
                            break;
                        case 'http://gedcomx.org/Female':
                            $sex = 'F';
                            break;
                    }
                }

                // Extract facts (events) from GedcomX format
                $birthday = null;
                $birth_month = null;
                $birth_year = null;
                $birthday_plac = null;
                $deathday = null;
                $death_month = null;
                $death_year = null;
                $deathday_plac = null;

                if (isset($person['facts']) && is_array($person['facts'])) {
                    foreach ($person['facts'] as $fact) {
                        $factType = $fact['type'] ?? '';
                        $date = $fact['date'] ?? null;
                        $place = $fact['place'] ?? null;

                        if ($factType === 'http://gedcomx.org/Birth') {
                            if ($date && isset($date['original'])) {
                                $birthday = self::parseGedcomXDate($date['original']);
                                if ($birthday) {
                                    $birth_month = date('n', strtotime($birthday));
                                    $birth_year = date('Y', strtotime($birthday));
                                }
                            }
                            if ($place && isset($place['original'])) {
                                $birthday_plac = $place['original'];
                            }
                        } elseif ($factType === 'http://gedcomx.org/Death') {
                            if ($date && isset($date['original'])) {
                                $deathday = self::parseGedcomXDate($date['original']);
                                if ($deathday) {
                                    $death_month = date('n', strtotime($deathday));
                                    $death_year = date('Y', strtotime($deathday));
                                }
                            }
                            if ($place && isset($place['original'])) {
                                $deathday_plac = $place['original'];
                            }
                        }
                    }
                }

                if ($givn == '') {
                    $givn = $name;
                }

                $value = [
                    'gid'             => $g_id,
                    'name'            => $name,
                    'givn'            => $givn,
                    'surn'            => $surn,
                    'sex'             => $sex,
                    'uid'             => $uid,
                    'rin'             => null,
                    'resn'            => null,
                    'rfn'             => null,
                    'afn'             => null,
                    'nick'            => null,
                    'type'            => null,
                    'chan'            => null,
                    'nsfx'            => null,
                    'npfx'            => null,
                    'spfx'            => null,
                    'birthday'        => self::validateDate($birthday) ? $birthday : null,
                    'birth_month'     => $birth_month,
                    'birth_year'      => $birth_year,
                    'birthday_dati'   => null,
                    'birthday_plac'   => $birthday_plac,
                    'deathday'        => $deathday,
                    'death_month'     => $death_month,
                    'death_year'      => $death_year,
                    'deathday_dati'   => null,
                    'deathday_plac'   => $deathday_plac,
                    'deathday_caus'   => null,
                    'burial_day'      => null,
                    'burial_month'    => null,
                    'burial_year'     => null,
                    'burial_day_dati' => null,
                    'burial_day_plac' => null,
                    'titl'            => null,
                    'famc'            => null,
                    'fams'            => null,
                    'chr'             => null,
                    'team_id'         => $tenant,
                ];

                $parentData[] = $value;
            }

            $chunk = array_chunk($parentData, 500);

            foreach ($chunk as $item) {
                app(BatchData::class)->upsert(Person::class, $conn, $item, ['uid']);
            }

            return $parentData;
        } catch (\Exception $e) {
            return \Log::error($e);
        }
    }

    /**
     * Parse GedcomX date format to standard date format
     *
     * @param string $gedcomxDate
     * @return string|null
     */
    private static function parseGedcomXDate($gedcomxDate)
    {
        if (empty($gedcomxDate)) {
            return null;
        }

        // Handle various GedcomX date formats
        // Simple date format: "1990-01-15"
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $gedcomxDate)) {
            return $gedcomxDate;
        }

        // Year only: "1990"
        if (preg_match('/^\d{4}$/', $gedcomxDate)) {
            return $gedcomxDate . '-01-01';
        }

        // Month and year: "1990-01"
        if (preg_match('/^\d{4}-\d{2}$/', $gedcomxDate)) {
            return $gedcomxDate . '-01';
        }

        // Try to parse other formats
        $timestamp = strtotime($gedcomxDate);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Optimized GedcomX person processing using PHP 8.4 features
     */
    public static function getPersonFromGedcomXOptimized($conn, $persons, $obje_ids = [], $sour_ids = [], $tenant = null): array
    {
        if (empty($persons)) {
            return [];
        }

        // PHP 8.4: Use constants for better performance
        $genderMap = [
            'http://gedcomx.org/Male' => 'M',
            'http://gedcomx.org/Female' => 'F',
        ];

        try {
            // PHP 8.4: Pre-allocate array with known size for better memory management
            $parentData = [];
            $batchSize = 1000;

            // Process in batches for better memory efficiency
            $chunks = array_chunk($persons, $batchSize);

            foreach ($chunks as $chunk) {
                $chunkData = [];

                foreach ($chunk as $person) {
                    $g_id = $person['id'] ?? '';
                    if (!$g_id) continue;

                    // PHP 8.4: Use array destructuring for better performance
                    [$name, $givn, $surn] = self::extractNamesOptimized($person['names'] ?? []);

                    // PHP 8.4: Use match expression for better performance
                    $sex = match (true) {
                        isset($person['gender']['type']) => $genderMap[$person['gender']['type']] ?? '',
                        default => ''
                    };

                    // PHP 8.4: Optimized fact extraction
                    [$birthday, $birth_month, $birth_year, $birthday_plac, $deathday, $death_month, $death_year, $deathday_plac] = 
                        self::extractFactsOptimized($person['facts'] ?? []);

                    $uid = strtoupper(str_replace('-', '', (string) \Illuminate\Support\Str::uuid()));

                    $chunkData[] = [
                        'gid'             => $g_id,
                        'name'            => $name,
                        'givn'            => $givn ?: $name,
                        'surn'            => $surn,
                        'sex'             => $sex,
                        'uid'             => $uid,
                        'rin'             => null,
                        'resn'            => null,
                        'rfn'             => null,
                        'afn'             => null,
                        'nick'            => null,
                        'type'            => null,
                        'chan'            => null,
                        'nsfx'            => null,
                        'npfx'            => null,
                        'spfx'            => null,
                        'birthday'        => self::validateDate($birthday) ? $birthday : null,
                        'birth_month'     => $birth_month,
                        'birth_year'      => $birth_year,
                        'birthday_dati'   => null,
                        'birthday_plac'   => $birthday_plac,
                        'deathday'        => $deathday,
                        'death_month'     => $death_month,
                        'death_year'      => $death_year,
                        'deathday_dati'   => null,
                        'deathday_plac'   => $deathday_plac,
                        'deathday_caus'   => null,
                        'burial_day'      => null,
                        'burial_month'    => null,
                        'burial_year'     => null,
                        'burial_day_dati' => null,
                        'burial_day_plac' => null,
                        'titl'            => null,
                        'famc'            => null,
                        'fams'            => null,
                        'chr'             => null,
                        'team_id'         => $tenant,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }

                if (!empty($chunkData)) {
                    // Use optimized batch processing
                    BatchData::upsert(Person::class, $conn, $chunkData, ['uid']);
                    $parentData = array_merge($parentData, $chunkData);
                }

                // Force garbage collection for large datasets
                if (memory_get_usage() > 256 * 1024 * 1024) { // 256MB
                    gc_collect_cycles();
                }
            }

            return $parentData;
        } catch (\Exception $e) {
            \Log::error('GedcomX person processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Optimized name extraction using PHP 8.4 features
     */
    private static function extractNamesOptimized(array $names): array
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

            // PHP 8.4: Use match for better performance
            match ($type) {
                'http://gedcomx.org/Given' => $givn = $value,
                'http://gedcomx.org/Surname' => $surn = $value,
                default => null
            };
        }

        return [$name, $givn, $surn];
    }

    /**
     * Optimized facts extraction using PHP 8.4 features
     */
    private static function extractFactsOptimized(array $facts): array
    {
        $result = [null, null, null, null, null, null, null, null];

        foreach ($facts as $fact) {
            $factType = $fact['type'] ?? '';
            $date = $fact['date']['original'] ?? null;
            $place = $fact['place']['original'] ?? null;

            // PHP 8.4: Use match for better performance
            match ($factType) {
                'http://gedcomx.org/Birth' => [
                    $result[0] = self::parseGedcomXDateOptimized($date),
                    $result[1] = $result[0] ? (int)date('n', strtotime($result[0])) : null,
                    $result[2] = $result[0] ? (int)date('Y', strtotime($result[0])) : null,
                    $result[3] = $place
                ],
                'http://gedcomx.org/Death' => [
                    $result[4] = self::parseGedcomXDateOptimized($date),
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
    private static function parseGedcomXDateOptimized(?string $gedcomxDate): ?string
    {
        if (!$gedcomxDate) {
            return null;
        }

        // PHP 8.4: Use match for better performance than if/else chains
        return match (true) {
            preg_match('/^\d{4}-\d{2}-\d{2}$/', $gedcomxDate) => $gedcomxDate,
            preg_match('/^\d{4}$/', $gedcomxDate) => $gedcomxDate . '-01-01',
            preg_match('/^\d{4}-\d{2}$/', $gedcomxDate) => $gedcomxDate . '-01',
            default => ($timestamp = strtotime($gedcomxDate)) !== false ? date('Y-m-d', $timestamp) : null
        };
    }

    private static function validateDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }
}
