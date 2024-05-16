<?php

namespace FamilyTree365\LaravelGedcom\Utils;

class BatchData
{

    public static function upsert($modelClass, $conn, array $values, array $uniqueBy, array $update = [])
    {
        return app($modelClass)->on($conn)->upsert($values, $uniqueBy, $update);
    }
}