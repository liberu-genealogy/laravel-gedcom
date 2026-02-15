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

    public static function getFamily($conn, $families, $obje_ids = [], $sour_ids = [], $persons_id = [], $note_ids = [], $repo_ids = [], $parentData = [], $tenant = null)
    {
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

                foreach ($children as $child) {
                    if (!isset($persons_id[$child])) {
                        $persons_id[$child] = app(Person::class)->where('gid', $child)->first()->id;
                    }
                }

                $husband_key = $parentData ? array_search($husb, array_column($parentData, 'gid')) : null;
                $husband_uid = $parentData[$husband_key]['uid'] ?? null;
                $husband = $husband_uid ? app(Person::class)->where('uid', $husband_uid)->first() : null;
                $husband_id = $husband?->id;

                $wife_key = $parentData ? array_search($wife, array_column($parentData, 'gid')) : null;
                $wife_uid = $parentData[$wife_key]['uid'] ?? null;
                $wife = $wife_uid ? app(Person::class)->where('uid', $wife_uid)->first() : null;
                $wife_id = $wife?->id;

                $persons_id[$husb] = $husband_id;
                $persons_id[$wife] = $wife_id;

                $value = [
                    'husband_id'  => $husband_id,
                    'wife_id'     => $wife_id,
                    'description' => $description,
                    'type_id'     => $type_id,
                    'nchi'        => $nchi,
                    'rin'         => $rin,
                    'team_id'     => $tenant,
                ];

                app(Family::class)->on($conn)->updateOrCreate($value, $value);
            }
            otherFamRecord::insertFamilyData($conn, $persons_id, $families, $obje_ids, $sour_ids);
        } catch (\Exception $e) {
            $error = $e->getMessage();

            return \Log::error($error);
        }
    }

    /**
     * Process GedcomX relationships data and convert to Laravel family models
     *
     * @param mixed $conn Database connection
     * @param array $relationships Array of GedcomX relationship objects
     * @param array $obje_ids Media object IDs mapping
     * @param array $sour_ids Source IDs mapping
     * @param array $persons_id Person IDs mapping
     * @param array $note_ids Note IDs mapping
     * @param array $repo_ids Repository IDs mapping
     * @param array $parentData Parent data from person processing
     * @param mixed $tenant Tenant information
     * @return void
     */
    public static function getFamilyFromGedcomX($conn, $relationships, $obje_ids = [], $sour_ids = [], $persons_id = [], $note_ids = [], $repo_ids = [], $parentData = [], $tenant = null)
    {
        try {
            foreach ($relationships as $relationship) {
                $relationshipType = $relationship['type'] ?? '';

                // Only process couple relationships for families
                if ($relationshipType !== 'http://gedcomx.org/Couple') {
                    continue;
                }

                $person1 = $relationship['person1'] ?? null;
                $person2 = $relationship['person2'] ?? null;

                if (!$person1 || !$person2) {
                    continue;
                }

                // Extract person references
                $person1_ref = $person1['resource'] ?? $person1['resourceId'] ?? '';
                $person2_ref = $person2['resource'] ?? $person2['resourceId'] ?? '';

                // Clean up references (remove # if present)
                $person1_ref = ltrim($person1_ref, '#');
                $person2_ref = ltrim($person2_ref, '#');

                // Find the persons in parentData
                $person1_key = $parentData ? array_search($person1_ref, array_column($parentData, 'gid')) : false;
                $person2_key = $parentData ? array_search($person2_ref, array_column($parentData, 'gid')) : false;

                $person1_uid = $person1_key !== false ? $parentData[$person1_key]['uid'] ?? null : null;
                $person2_uid = $person2_key !== false ? $parentData[$person2_key]['uid'] ?? null : null;

                // Get person IDs from database
                $person1_id = null;
                $person2_id = null;

                if ($person1_uid) {
                    $person1_record = app(Person::class)->on($conn)->where('uid', $person1_uid)->first();
                    $person1_id = $person1_record ? $person1_record->id : null;
                }

                if ($person2_uid) {
                    $person2_record = app(Person::class)->on($conn)->where('uid', $person2_uid)->first();
                    $person2_id = $person2_record ? $person2_record->id : null;
                }

                // Determine husband and wife based on gender (if available)
                $husband_id = null;
                $wife_id = null;

                if ($person1_key !== false && $person2_key !== false) {
                    $person1_sex = $parentData[$person1_key]['sex'] ?? '';
                    $person2_sex = $parentData[$person2_key]['sex'] ?? '';

                    if ($person1_sex === 'M') {
                        $husband_id = $person1_id;
                        $wife_id = $person2_id;
                    } elseif ($person1_sex === 'F') {
                        $wife_id = $person1_id;
                        $husband_id = $person2_id;
                    } elseif ($person2_sex === 'M') {
                        $husband_id = $person2_id;
                        $wife_id = $person1_id;
                    } elseif ($person2_sex === 'F') {
                        $wife_id = $person2_id;
                        $husband_id = $person1_id;
                    } else {
                        // If gender is unknown, assign arbitrarily
                        $husband_id = $person1_id;
                        $wife_id = $person2_id;
                    }
                }

                // Extract facts for marriage events
                $description = null;
                if (isset($relationship['facts']) && is_array($relationship['facts'])) {
                    foreach ($relationship['facts'] as $fact) {
                        $factType = $fact['type'] ?? '';
                        if ($factType === 'http://gedcomx.org/Marriage') {
                            $date = $fact['date']['original'] ?? null;
                            $place = $fact['place']['original'] ?? null;
                            if ($date || $place) {
                                $description = trim(($date ?? '') . ' ' . ($place ?? ''));
                            }
                            break;
                        }
                    }
                }

                $value = [
                    'husband_id'  => $husband_id,
                    'wife_id'     => $wife_id,
                    'description' => $description,
                    'type_id'     => 0,
                    'nchi'        => null,
                    'rin'         => null,
                    'team_id'     => $tenant,
                ];

                // Only create family if we have at least one valid person
                if ($husband_id || $wife_id) {
                    app(Family::class)->on($conn)->updateOrCreate($value, $value);
                }
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            return \Log::error($error);
        }
    }
}
