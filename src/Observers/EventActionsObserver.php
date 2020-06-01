<?php

namespace Asdfx\LaravelGedcom\Observers;

use Asdfx\LaravelGedcom\Utils\DateParser;

class EventActionsObserver
{
    public function saving($model)
    {
        $parser = new DateParser($model->date);
        $model->fill($parser->parse_date());
    }
}