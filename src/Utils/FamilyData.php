<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use FamilyTree365\LaravelGedcom\Models\Family;

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

    public static function getFamily($conn, $families, $obje_ids, $sour_ids, $persons_id, $note_ids, $repo_ids)
    {
        $familyData = [];
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

                $husband_id = (isset($husb) ? $husb : 0);
                $wife_id = (isset($wife) ? $wife : 0);

                //        $family = Family::on($conn)->updateOrCreate(
                //            compact('husband_id', 'wife_id', 'description', 'type_id', 'nchi', 'rin')
                //        );

                // $value = ['husband_id'=>$husband_id, 'wife_id'=>$wife_id, 'description'=>$description, 'type_id'=>$type_id, 'nchi'=>$nchi, 'rin'=>$rin];
                // $familydata [] = $value;
            }
            // Family::insert($familyData);

            $key = [
                ['husband_id', $husband_id], ['wife_id', $wife_id], ['description', $description], ['type_id', $type_id], ['nchi', $nchi],
                ['rin', $rin]
            ];
            $check = Family::on($conn)->where($key)->first();
            if (empty($check)) {
                $value = [['husband_id', $husband_id], ['wife_id', $wife_id], ['description', $description], ['type_id', $type_id], ['nchi', $nchi],
                    ['rin', $rin]];

                $FamilyData[] = $value;
            }
            // $person = Person::on($conn)->updateOrCreate($key,$value);
            // otherFields::insertOtherFields($conn,$individual,$obje_ids,$person);


            foreach (array_chunk($FamilyData, 200) as $chunk) {
                Family::on($conn)->insert($chunk);
            }
            otherFamRecord::insertFamilyData($conn, $families, $obje_ids, $sour_ids);

        } catch (\Exception $e) {
            $error = $e->getMessage();
            return \Log::error($error);
        }

    }
}

