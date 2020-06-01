<?php

namespace Asdfx\LaravelGedcom\Models;

use Asdfx\LaravelGedcom\Concerns\AccessesProtectedProperties;
use Asdfx\LaravelGedcom\Concerns\NotableModel;
use Asdfx\LaravelGedcom\Concerns\UuidModel;
use Asdfx\LaravelGedcom\Contracts\GedcomModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use PhpGedcom\Record\Indi;

class Individual extends Model implements GedcomModel
{
    use AccessesProtectedProperties, UuidModel, NotableModel;

    protected $table = 'individuals';

    /**
     * @param Indi $record
     * @return Model
     */
    public static function createFromGedcom($record): Model
    {
        $model = new self();
        $model->uid = $record->getUid();
        $model->sex = $record->getSex();
        $name = Arr::get($record->getName(), '0', '');
        if ($name !== '') {
            $model->given_name = $model->accessProtected($name, '_givn');
            $model->surname = $model->accessProtected($name, '_surn');
        }

        $events = [];
        foreach ($record->getAllEven() as $event) {
            $eventModel = Event::createFromGedcom($event);
            if ($eventModel !== null) {
                $events[] = $eventModel;
            }
        }

        foreach($record->getNote() as $note) {
            $noteData = $model->accessProtected($note, '_note');
            $model->notes()->create(['notes' => $noteData]);
        }

        $model->events()->saveMany($events);

        return $model;
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}