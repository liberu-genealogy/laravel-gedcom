<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer;

use FamilyTree365\LaravelGedcom\Models\Media;
use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Utils\Importer\Obje;
use Illuminate\Support\Facades\DB;

class MediaParser
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
    {
        $media = new Media();
        $media->file = $mediaObject->getFile();
        $media->title = $mediaObject->getTitle();
        $media->note = $mediaObject->getNote();
        $media->save();

        return $media->id;
    }

    protected function linkToIndividuals($mediaId, $individualIds)
    {
        foreach ($individualIds as $indiId) {
            $person = Person::where('gedcom_id', $indiId)->first();
            if ($person) {
                $person->media()->attach($mediaId);
            }
        }
    }

    protected function linkToFamilies($mediaId, $familyIds)
    {
        foreach ($familyIds as $famId) {
            $family = Family::where('gedcom_id', $famId)->first();
            if ($family) {
                $family->media()->attach($mediaId);
            }
        }
    }
}
