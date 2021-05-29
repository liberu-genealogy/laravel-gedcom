<?php

namespace FamilyTree365\LaravelGedcom\Observers;

use FamilyTree365\LaravelGedcom\Utils\DateParser;

class EventActionsObserver
{
    public function saving($model)
    {
        $parser = new DateParser($model->date);
        $model->fill($parser->parse_date());
    }
}
