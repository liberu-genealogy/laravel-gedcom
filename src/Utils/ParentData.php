<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Models\Person;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

readonly class ParentData
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

    public static function getPerson(
        string $conn,
        array $individuals,
        array $objeIds,
        array $sourIds
    ): array {
        return DB::transaction(function () use ($conn, $individuals, $objeIds, $sourIds) {
            $parentData = [];

            foreach ($individuals as $individual) {
                $parentData[] = self::processIndividual($conn, $individual);
            }

            self::batchInsertData($conn, $parentData);
            self::processOtherFields($conn, $individuals, $objeIds, $sourIds);

            return $parentData;
        });
    }

    private static function processIndividual(string $conn, object $individual): array
    {
        $names = $individual->getName();
        $firstNameRecord = $names ? current($names) : null;

        return [
            'gid' => $individual->getId(),
            'name' => $firstNameRecord?->getName() ?? '',
            'givn' => $firstNameRecord?->getGivn() ?? '',
            'surn' => $firstNameRecord?->getSurn() ?? '',
            'sex' => self::sanitizeSex($individual->getSex()),
            'uid' => $individual->getUid() ?? Str::uuid(),
            'rin' => $individual->getRin() ?? '',
            'resn' => $individual->getResn() ?? '',
            'rfn' => $individual->getRfn() ?? '',
            'afn' => $individual->getAfn() ?? '',
            'nick' => $firstNameRecord?->getNick() ?? '',
            'type' => $firstNameRecord?->getType() ?? '',
            'chan' => $individual->getChan() ? $individual->getChan()->getDatetime() : null,
            'nsfx' => $firstNameRecord?->getNsfx() ?? '',
            'npfx' => $firstNameRecord?->getNpfx() ?? '',
            'spfx' => $firstNameRecord?->getSpfx() ?? '',
            'birthday' => self::validateDate($individual->getBirt()) ? $individual->getBirt()->dateFormatted : null,
            'birth_month' => $individual->getBirt() ? $individual->getBirt()->month : null,
            'birth_year' => $individual->getBirt() ? $individual->getBirt()->year : null,
            'birthday_dati' => mb_convert_encoding((string) $individual->getBirt()->dati, 'UTF-8', 'ISO-8859-1'),
            'birthday_plac' => mb_convert_encoding((string) $individual->getBirt()->plac, 'UTF-8', 'ISO-8859-1'),
            'deathday' => self::validateDate($individual->getDeat()) ? $individual->getDeat()->dateFormatted : null,
            'death_month' => $individual->getDeat() ? $individual->getDeat()->month : null,
            'death_year' => $individual->getDeat() ? $individual->getDeat()->year : null,
            'deathday_dati' => $individual->getDeat() ? $individual->getDeat()->dati : null,
            'deathday_plac' => mb_convert_encoding((string) $individual->getDeat()->plac, 'UTF-8', 'ISO-8859-1'),
            'deathday_caus' => $individual->getDeat() ? $individual->getDeat()->caus : null,
            'burial_day' => self::validateDate($individual->getBuri()) ? $individual->getBuri()->dateFormatted : null,
            'burial_month' => $individual->getBuri() ? $individual->getBuri()->month : null,
            'burial_year' => $individual->getBuri() ? $individual->getBuri()->year : null,
            'burial_day_dati' => $individual->getBuri() ? $individual->getBuri()->dati : null,
            'burial_day_plac' => $individual->getBuri() ? $individual->getBuri()->plac : null,
            'titl' => array_key_exists('TITL', $individual->getAllAttr()) ? $individual->getAllAttr()['TITL'][0]->getAttr('TITL') : null,
            'famc' => $individual->getFamc() ? $individual->getFamc()[0]->getFamc() : null,
            'fams' => $individual->getFams() ? $individual->getFams()[0]->getFams() : null,
            'chr' => self::validateDate($individual->getChr()) ? $individual->getChr()->dateFormatted : null,
        ];
    }

    private static function sanitizeSex(?string $sex): string
    {
        return preg_replace('/[^MF]/', '', (string) $sex) ?: '';
    }

    private static function batchInsertData(string $conn, array $data): void
    {
        $chunk = array_chunk($data, 500);

        foreach ($chunk as $item) {
            app(BatchData::class)->upsert(Person::class, $conn, $item, ['uid']);
        }
    }

    private static function processOtherFields(string $conn, array $individuals, array $objeIds, array $sourIds): void
    {
        otherFields::insertOtherFields($conn, $individuals, $objeIds, $sourIds);
    }

    private static function validateDate(?object $date, string $format = 'Y-m-d'): bool
    {
        if ($date === null) {
            return false;
        }
        $d = \DateTime::createFromFormat($format, $date->dateFormatted);
        return $d && $d->format($format) === $date->dateFormatted;
    }
}