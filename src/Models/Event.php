<?php

namespace Asdfx\LaravelGedcom\Models;

use Asdfx\LaravelGedcom\Concerns\AccessesProtectedProperties;
use Asdfx\LaravelGedcom\Concerns\NotableModel;
use Asdfx\LaravelGedcom\Contracts\GedcomModel;
use Illuminate\Database\Eloquent\Model;
use PhpGedcom\Record\Indi\Birt;
use PhpGedcom\Record\Indi\Buri;
use PhpGedcom\Record\Indi\Chr;
use PhpGedcom\Record\Indi\Deat;
use PhpGedcom\Record\Indi\Even;

class Event extends Model implements GedcomModel
{
    use AccessesProtectedProperties, NotableModel;

    /**
     * @param Even $record
     * @return Model|null
     */
    public static function createFromGedcom($record): ?Model
    {
        $model = new self();
        if (get_class($record) === Even::class) {
            return null;
        }

        if (get_class($record) === Birt::class) {
            $model->type = 'Birth';
        }

        if (get_class($record) === Deat::class) {
            $model->type = 'Death';
        }

        if (get_class($record) === Buri::class) {
            $model->type = 'Burial';
        }

        if (get_class($record) === Chr::class) {
            $model->type = 'Christening';
        }

        if ($record->getPlac() !== null) {
            $model->place = $model->accessProtected($record->getPlac(), 'plac');
        }

        $model->date = $record->getDate();

        foreach($record->getNote() as $note) {
            $noteData = $model->accessProtected($note, '_note');
            $model->notes()->create(['notes' => $noteData]);
        }

        $model->save();

        return $model;
    }
}