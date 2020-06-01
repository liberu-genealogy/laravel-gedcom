<?php

namespace Asdfx\LaravelGedcom\Contracts;

use Illuminate\Database\Eloquent\Model;
use PhpGedcom\Record;

interface GedcomModel
{
    public static function createFromGedcom($record): ?Model;
}