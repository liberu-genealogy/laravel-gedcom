<?php

namespace Asdfx\LaravelGedcom\Models;

use Asdfx\LaravelGedcom\Concerns\AccessesProtectedProperties;
use Asdfx\LaravelGedcom\Contracts\GedcomModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PhpGedcom\Record\Indi\Birt;
use PhpGedcom\Record\Indi\Deat;

class Event extends Model implements GedcomModel
{
    use AccessesProtectedProperties;

    public static function createFromGedcom($record): Model
    {
        $model = new self();
        if (get_class($record) === Birt::class) {
            $model->type = 'Birth';
        }

        if (get_class($record) === Deat::class) {
            $model->type = 'Death';
        }

        $model->place = $model->accessProtected($record->getPlac(), 'plac');
        $model->date = Carbon::parse($record->getDate());

        $model->save();

        return $model;
    }
}