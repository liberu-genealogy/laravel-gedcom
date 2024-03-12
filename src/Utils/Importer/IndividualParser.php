<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer;

use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Utils\FamilyData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndividualParser
{
    protected $conn;
    protected $individualIds = [];

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function parseIndividuals($individuals)
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

    protected function parseAttributes($individual)
    {
        $person = new Person();
        $person->name = $this->parseName($individual);
        $person->sex = $individual->getSex();
        $person->birth_date = $this->parseBirth($individual);
        $person->death_date = $this->parseDeath($individual);
        $person->save();
        $this->individualIds[$individual->getId()] = $person->id;
    }

    protected function parseName($individual)
    {
        return $individual->getName() ? $individual->getName()->getFullName() : null;
    }

    protected function parseBirth($individual)
    {
        return $individual->getBirth() ? $individual->getBirth()->getDate() : null;
    }

    protected function parseDeath($individual)
    {
        return $individual->getDeath() ? $individual->getDeath()->getDate() : null;
    }

    protected function parseRelationships($conn, $individual)
    {
        $familyLinks = FamilyData::getFamilyLinks($conn, $individual->getId());
        foreach ($familyLinks as $familyLink) {
            // Logic to parse and store relationships
            // This could involve linking to parents, spouses, and children
        }
    }
}
