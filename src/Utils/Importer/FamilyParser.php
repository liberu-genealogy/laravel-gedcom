<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer;

use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Utils\FamilyData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FamilyParser
/**
 * FamilyParser class is responsible for parsing family data from GEDCOM files.
 */
{
    protected $conn;
    protected $familyIds = [];

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function parseFamilies($families)
    {
        foreach ($families as $family) {
            DB::beginTransaction();
            try {
                $this->parseAttributes($family);
                $this->parseRelationships($this->conn, $family);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to parse family: ' . $e->getMessage());
            }
        }
    }

    protected function parseAttributes($family)
    /**
     * Parses families from GEDCOM files and processes their data.
     * 
     * @param array $families Array of families to be parsed.
     * 
     * This method does not return anything but processes family data.
     */
    {
        $fam = new Family();
        $fam->marriage_date = $family->getMarr() ? $family->getMarr()->getDate() : null;
        $fam->marriage_place = $family->getMarr() ? $family->getMarr()->getPlac() : null;
        $fam->save();
        $this->familyIds[$family->getId()] = $fam->id;
    }

    protected function parseRelationships($conn, $family)
    {
        FamilyData::linkSpousesAndChildren($conn, $family->getId(), $this->familyIds);
    }
}
    /**
     * Parses and links relationships within a family.
     * 
     * @param mixed $conn Database connection.
     * @param object $family Family object to process relationships for.
     * 
     * This method does not return anything but contributes to parsing family data.
     */
    /**
     * Parses attributes of a family and saves them to the database.
     * 
     * @param object $family Family object to parse attributes for.
     * 
     * This method does not return anything but contributes to parsing family data.
     */
