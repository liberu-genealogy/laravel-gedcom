<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;

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
        } else {
            if (is_array($phon)) {
                $ret = '';
                $ret = json_encode($phon);

                return $ret;
            } else {
                return "$phon";
            }
        }
    }
}
