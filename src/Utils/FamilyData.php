<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Models\Person;

class FamilyData
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new cyrus_authenticate(connection)                                   sincrement ID.
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

    public static function getFamily($conn, $families, $obje_ids = [], $sour_ids = [], $persons_id = [], $note_ids = [], $repo_ids = [], $parentData = [])
    {
        $familyData = [];
        $persons_id = [];

        try {
            foreach ($families as $family) {
                $g_id = $family->getId();
                $resn = $family->getResn();
                $husb = $family->getHusb();
                $wife = $family->getWife();

                // string
                $nchi = $family->getNchi();
                $rin = $family->getRin();

                // array
                $subm = $family->getSubm();
                $_slgs = $family->getSlgs();

                $description = null;
                $type_id = 0;

                $children = $family->getChil();
                $events = $family->getAllEven();
                $_note = $family->getNote();
                $_obje = $family->getObje();
                $_sour = $family->getSour();
                $_refn = $family->getRefn();

                $chan = $family->getChan();

                $husband_key = $parentData? array_search($husb, array_column($parentData, 'gid')) : null;
                $husband_key = array_search($husb, array_column($parentData, 'gid'));
                $husband_uid = $parentData[$husband_key]['uid'] ?? null;
                $husband_id = Person::where('uid', $husband_uid)->first()->id ?? null;

                $wife_key = $parentData? array_search($wife, array_column($parentData, 'gid')): null;
                $wife_uid = $parentData[$wife_key]['uid'] ?? null;
                $wife_id = Person::where('uid', $wife_uid)->first()->id ?? null;

                $persons_id[$husb] = $husband_id;
                $persons_id[$wife] = $wife_id;

                $value = [
                    'husband_id'  => $husband_id,
                    'wife_id'     => $wife_id,
                    'description' => $description,
                    'type_id'     => $type_id,
                    'nchi'        => $nchi,
                    'rin'         => $rin,
                ];

                Family::on($conn)->updateOrCreate($value, $value);
            }

            otherFamRecord::insertFamilyData($conn, $persons_id, $families, $obje_ids, $sour_ids);
        } catch (\Exception $e) {
            $error = $e->getMessage();

            return \Log::error($error);
        }
    }
}
