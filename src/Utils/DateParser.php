<?php

namespace ModularSoftware\LaravelGedcom\Utils;

class DateParser
{
    private $date_string;
    private $year = null;
    private $month = null;
    private $day = null;

    public function __construct($date_string = '')
    {
        $this->date_string = $date_string;
    }

    public function parse_date()
    {
        $this->trim_datestring();
        if (!$this->try_parse_full_date()) {
            if (!$this->try_parse_M_Y_date()) {
                if (!$this->try_parse_Y_date()) {
                    $this->set_null_date();
                }
            }
        }

        return $this->export();
    }

    private function trim_datestring()
    {
        $words_to_remove = [
            // If an exact date is not known, a date range can be specified
            'AFT', 'BEF', 'BET', 'AND',
            //For approximate dates
            'ABT', 'CAL', 'EST',
            // Takes a property over a certain period of time ( e.g. exercise of a profession, living in a particular place )
            'FROM', 'TO',
        ];
        foreach ($words_to_remove as $word) {
            $this->date_string = str_replace($word, '', $this->date_string);
        }
        $this->date_string = trim($this->date_string);
    }

    private function try_parse_full_date()
    {
        $this->set_null_date(); // Default
        $date_parts = explode(' ', $this->date_string);
        if (count($date_parts) > 3) {
            return false;
        }

        return $this->try_get_day(
            $date_parts[0] ?? false
        )
            and
            $this->try_get_month(
                $date_parts[1] ?? false
            )
            and
            $this->try_get_year(
                $date_parts[2] ?? false,
                $date_parts[3] ?? false
            );
    }

    private function try_parse_M_Y_date()
    {
        $this->set_null_date(); // Default
        $date_parts = explode(' ', $this->date_string);
        if (count($date_parts) > 3) {
            return false;
        }

        return $this->try_get_month(
            $date_parts[0] ?? false
        )
            and
            $this->try_get_year(
                $date_parts[1] ?? false,
                $date_parts[2] ?? false
            );
    }

    private function try_parse_Y_date()
    {
        $this->set_null_date(); // Default
        $date_parts = explode(' ', $this->date_string);
        if (count($date_parts) > 2) {
            return false;
        }

        return $this->try_get_year(
            $date_parts[0] ?? false,
            $date_parts[1] ?? false
        );
    }

    private function try_get_year($year, $epoch = false)
    {
        $sign = 1;
        if ($epoch) {
            if ($epoch == 'BC') {
                $sign = -1;
            } elseif ($epoch == 'AC') {
                $sign = 1;
            } else {
                return false;
            }
        }
        if (!$year) {
            return false;
        }
        if (is_numeric($year)) {
            $this->year = $year * $sign;
        } else {
            return false;
        }

        return true;
    }

    private function try_get_month($month)
    {
        $months = [
            'JAN' => 1,
            'FEB' => 2,
            'MAR' => 3,
            'APR' => 4,
            'MAY' => 5,
            'JUN' => 6,
            'JUL' => 7,
            'AUG' => 8,
            'SEP' => 9,
            'OCT' => 10,
            'NOV' => 11,
            'DEC' => 12,
        ];

        if (isset($months[$month])) {
            $this->month = $months[$month];

            return true;
        } else {
            return false;
        }
    }

    private function try_get_day($day)
    {
        if (is_numeric($day)) {
            $this->day = $day * 1;

            return true;
        } else {
            return false;
        }
    }

    public function set_null_date()
    {
        $this->year = null;
        $this->month = null;
        $this->day = null;
    }

    public function export()
    {
        return [
            'year'          => $this->year,
            'month'         => $this->month,
            'day'           => $this->day,
        ];
    }
}
