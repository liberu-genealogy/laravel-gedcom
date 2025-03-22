<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer;

use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Utils\FamilyData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class IndividualParser
/**
 * IndividualParser class is responsible for parsing individual data from GEDCOM files.
 */
{
    private string $conn;
    protected array $individualIds = [];

    public function __construct(
        private string $conn
    ) {}

    public function parseIndividuals(array $individuals): void
    {
        foreach ($individuals as $individual) {
            DB::beginTransaction();
            try {
                $this->parseAttributes($individual);
                $this->parseRelationships($this->conn, $individual);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to parse individual: ' . $e->getMessage());
            }
        }
    }

    private function parseAttributes(object $individual): void
    {
        $person = app(Person::class);
        $person->fill([
            'name' => $this->parseName($individual),
            'sex' => $individual->getSex(),
            'birth_date' => $this->parseBirth($individual),
            'death_date' => $this->parseDeath($individual)
        ])->save();

        $this->individualIds[$individual->getId()] = $person->id;
    }

    private function parseName(object $individual): ?string
    {
        $names = $individual->getName();
        return $names ? $names[0]->getFullName() : null;
    }

    private function parseBirth(object $individual): ?string
    {
        return $individual->getBirth() ? $individual->getBirth()->getDate() : null;
    }

    private function parseDeath(object $individual): ?string
    {
        return $individual->getDeath() ? $individual->getDeath()->getDate() : null;
    }

    private function parseRelationships(string $conn, object $individual): void
    {
        $familyLinks = FamilyData::getFamilyLinks($conn, $individual->getId());
        foreach ($familyLinks as $familyLink) {
            // Logic to parse and store relationships
            // This could involve linking to parents, spouses, and children
        }
    }
}