<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils\Importer;

class Date
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID.
     *
     * @var string
     */
    public static function read($conn, $input_date)
    {
        if (is_object($input_date)) {
            if (method_exists($input_date, 'getDate')) {
                return $input_date->getDate();
            }
        } else {
            return "$input_date";
        }
    }
}
