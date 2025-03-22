<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer;

use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Utils\FamilyData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

readonly class IndividualParser
/**
 * IndividualParser class is responsible for parsing individual data from GEDCOM files.
 */
{
    private array $individualIds = [];

    public function __construct(
        private string $conn
    ) {}

    public function parseIndividuals(array $individuals): void
    {
        foreach ($individuals as $individual) {
            DB::transaction(function () use ($individual) {
                $this->parseAttributes($individual);
                $this->parseRelationships($individual);
            });
        }
    }

    private function parseAttributes(object $individual): void
    {
        $person = app(Person::class)->create([
            'name' => $this->extractName($individual),
            'sex' => $this->sanitizeSex($individual->getSex()),
            'birth_date' => $this->extractDate($individual->getBirth()),
            'death_date' => $this->extractDate($individual->getDeath()),
            'uid' => $individual->getUid() ?? Str::uuid(),
        ]);

        $this->individualIds[$individual->getId()] = $person->id;
    }

    protected function parseName(object $individual): string|null
    {
        return $individual->getName() ? $individual->getName()[0]->getFullName() : null;
    }

    protected function parseBirth(object $individual): string|null
    {
        return $individual->getBirth() ? $individual->getBirth()->getDate() : null;
    }

    protected function parseDeath(object $individual): string|null
    {
        return $individual->getDeath() ? $individual->getDeath()->getDate() : null;
    }

    protected function parseRelationships(object $individual): void
    {
        $familyLinks = FamilyData::getFamilyLinks($this->conn, $individual->getId());
        foreach ($familyLinks as $familyLink) {
            // Logic to parse and store relationships
            // This could involve linking to parents, spouses, and children
        }
    }
}