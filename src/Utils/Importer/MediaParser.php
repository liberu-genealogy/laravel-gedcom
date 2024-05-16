<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer;

use FamilyTree365\LaravelGedcom\Models\Media;
use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Utils\Importer\Obje;
use Illuminate\Support\Facades\DB;

class MediaParser
/**
 * MediaParser class is responsible for parsing media object data from GEDCOM files.
 */
{
    protected $conn;
    protected $mediaIds = [];

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function parseMediaObjects($mediaObjects)
    {
        foreach ($mediaObjects as $mediaObject) {
            $mediaId = $this->parseAttributes($mediaObject);
            if ($mediaId) {
                $this->mediaIds[] = $mediaId;
                $this->linkToIndividuals($mediaId, $mediaObject->getIndiIds());
                $this->linkToFamilies($mediaId, $mediaObject->getFamIds());
            }
        }
    }

    protected function parseAttributes($mediaObject)
    /**
     * Parses media objects from GEDCOM files and processes their data.
     * 
     * @param array $mediaObjects Array of media objects to be parsed.
     * 
     * This method does not return anything but processes media object data.
     */
    {
        $media = app(Media::class);
        $media->file = $mediaObject->getFile();
        $media->title = $mediaObject->getTitle();
        $media->note = $mediaObject->getNote();
        $media->save();

        return $media->id;
    }

    protected function linkToIndividuals($mediaId, $individualIds)
    {
        foreach ($individualIds as $indiId) {
            $person = app(Person::class)->where('gedcom_id', $indiId)->first();
            if ($person) {
                $person->media()->attach($mediaId);
            }
        }
    }

    protected function linkToFamilies($mediaId, $familyIds)
    {
        foreach ($familyIds as $famId) {
            $family = app(Family::class)->where('gedcom_id', $famId)->first();
            if ($family) {
                $family->media()->attach($mediaId);
            }
        }
    }
}
    /**
     * Parses attributes of a media object and saves them to the database.
     * 
     * @param object $mediaObject Media object to parse attributes for.
     * @return int The ID of the saved media object.
     */
    /**
     * Links a media object to individuals based on their IDs.
     * 
     * @param int $mediaId The ID of the media object.
     * @param array $individualIds Array of individual IDs to link the media to.
     * 
     * This method does not return anything but links media to individuals.
     */
    /**
     * Links a media object to families based on their IDs.
     * 
     * @param int $mediaId The ID of the media object.
     * @param array $familyIds Array of family IDs to link the media to.
     * 
     * This method does not return anything but links media to families.
     */
