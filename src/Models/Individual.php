<?php

namespace Asdfx\LaravelGedcom\Models;

use Asdfx\LaravelGedcom\Concerns\AccessesProtectedProperties;
use Asdfx\LaravelGedcom\Concerns\UuidModel;
use Asdfx\LaravelGedcom\Contracts\GedcomModel;
use Illuminate\Database\Eloquent\Model;

class Individual extends Model implements GedcomModel
{
    use AccessesProtectedProperties, UuidModel;

    protected $table = 'individuals';

    public static function createFromGedcom($record): Model
    {
        $model = new self();
        $model->uid = $record->getUid();
        $model->sex = $record->getSex();
        $name = $record->getName()[0];
        $model->given_name = $model->accessProtected($name, '_givn');
        $model->surname = $model->accessProtected($name, '_surn');
        $model->save();

        $events = [];
        foreach ($record->getAllEven() as $event) {
            $events[] = Event::createFromGedcom($event);
        }

        return $model;
    }
}