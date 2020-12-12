<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils;

use GenealogiaWebsite\LaravelGedcom\Models\Family;

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

    public static function getFamily($conn, $families, $obje_ids, $sour_ids)
    {
        $familyData = [];
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

            $husband_id = (isset($persons_id[$husb])) ? $persons_id[$husb] : 0;
            $wife_id = (isset($persons_id[$wife])) ? $persons_id[$wife] : 0;

            Family::on($conn)->updateOrCreate(
                compact('husband_id', 'wife_id', 'description', 'type_id', 'nchi', 'rin')
            );

            // $value = ['husband_id'=>$husband_id, 'wife_id'=>$wife_id, 'description'=>$description, 'type_id'=>$type_id, 'nchi'=>$nchi, 'rin'=>$rin];
            // $familydata [] = $value;
        }
        // Family::insert($familyData);
        otherFamRecord::insertFamilyData($conn, $families, $obje_ids, $sour_ids);
    }
}
