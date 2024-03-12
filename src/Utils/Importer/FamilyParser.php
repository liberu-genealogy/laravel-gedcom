<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer;

use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Utils\FamilyData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FamilyParser
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
