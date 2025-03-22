<?php

namespace FamilyTree365\LaravelGedcom\Utils;

readonly class DateParser
{
    private const MONTHS = [
        'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4,
        'MAY' => 5, 'JUN' => 6, 'JUL' => 7, 'AUG' => 8,
        'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12,
    ];

    private const DATE_QUALIFIERS = [
        'AFT', 'BEF', 'BET', 'AND', 'ABT', 'CAL', 'EST', 'FROM', 'TO'
    ];

    private int|float|null $year = null;
    private ?int $month = null;
    private int|float|null $day = null;
    private string|array|null $date_string = null;

    public function parse_date(): array
    {
        $dateString = $this->cleanDateString();

        return match(true) {
            $this->try_parse_full_date($dateString) => $this->exportDate(),
            $this->try_parse_M_Y_date($dateString) => $this->exportDate(),
            $this->try_parse_Y_date($dateString) => $this->exportDate(),
            default => $this->getNullDate()
        };
    }

    private function cleanDateString(): string
    {
        $string = str_replace(self::DATE_QUALIFIERS, '', $this->date_string ?? '');
        return trim($string);
    }

    private function try_parse_full_date()
    {
        $this->set_null_date(); // Default
        $date_parts = explode(' ', (string) $this->date_string);
        if (count($date_parts) > 3) {
            return false;
        }

        return $this->try_get_day(
            $date_parts[0] ?? false
        ) && $this->try_get_month(
            $date_parts[1] ?? false
        ) && $this->try_get_year(
            $date_parts[2] ?? false,
            $date_parts[3] ?? false
        );
    }

    private function try_parse_M_Y_date()
    {
        $this->set_null_date(); // Default
        $date_parts = explode(' ', (string) $this->date_string);
        if (count($date_parts) > 3) {
            return false;
        }

        return $this->try_get_month(
            $date_parts[0] ?? false
        ) && $this->try_get_year(
            $date_parts[1] ?? false,
            $date_parts[2] ?? false
        );
    }

    private function try_parse_Y_date()
    {
        $this->set_null_date(); // Default
        $date_parts = explode(' ', (string) $this->date_string);
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

    private function exportDate(): array
    {
        return [
            'year'          => $this->year,
            'month'         => $this->month,
            'day'           => $this->day,
        ];
    }

    private function getNullDate(): array
    {
        return [
            'year' => null,
            'month' => null,
            'day' => null
        ];
    }
}