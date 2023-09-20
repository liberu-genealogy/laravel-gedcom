<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer;

class Phon
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID.
     *
     * @var string
     */
    public static function read($conn, $phon)
    {
        if (is_object($phon)) {
            if (method_exists($phon, 'getPhon')) {
                return $phon->getPhon();
            }
        } elseif (is_array($phon)) {
            return json_encode($phon, JSON_THROW_ON_ERROR);
        } else {
            return "$phon";
        }
    }
}
