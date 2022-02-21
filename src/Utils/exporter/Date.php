<?php

namespace FamilyTree365\LaravelGedcom\Utils\Exporter;

use Carbon\Carbon;

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
            // $input_date = Carbon::parse($input_date)->timestamp;
            return "$input_date";
        }
    }
}
