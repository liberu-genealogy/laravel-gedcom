<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer\Indi\Even;

class Plac
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID.
     *
     * @var string
     */
    public static function read($conn, $place)
    {
        if (is_object($place)) {
            $place = $place->getPlac();
        }

        return $place;
    }
}
