<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer;

use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Utils\FamilyData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndividualParser
/**
 * IndividualParser class is responsible for parsing individual data from GEDCOM files.
 */
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
    /**
     * Parses individuals from GEDCOM files and processes their data.
     * 
     * @param array $individuals Array of individuals to be parsed.
     * 
     * This method does not return anything but processes individual data.
     */
    {
        $person = app(Person::class);
        $person->name = $this->parseName($individual);
        $person->sex = $individual->getSex();
        $person->birth_date = $this->parseBirth($individual);
        $person->death_date = $this->parseDeath($individual);
        $person->save();
        $this->individualIds[$individual->getId()] = $person->id;
    }

    protected function parseName($individual)
    {
        return $individual->getName() ? $individual->getName()[0]->getFullName() : null;
    }

    protected function parseBirth($individual)
    {
        return $individual->getBirth() ? $individual->getBirth()->getDate() : null;
    }

    protected function parseDeath($individual)
    /**
     * Parses attributes of an individual and saves them to the database.
     * 
     * @param object $individual Individual object to parse attributes for.
     * 
     * This method does not return anything but contributes to parsing individual data.
     */
    /**
     * Parses the name of an individual.
     * 
     * @param object $individual Individual object to parse the name for.
     * @return string|null The full name of the individual or null if not available.
     */
    /**
     * Parses the birth date of an individual.
     * 
     * @param object $individual Individual object to parse the birth date for.
     * @return string|null The birth date of the individual or null if not available.
     */
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
    /**
     * Parses the death date of an individual.
     * 
     * @param object $individual Individual object to parse the death date for.
     * @return string|null The death date of the individual or null if not available.
     */
