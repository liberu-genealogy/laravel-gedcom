<?php

namespace Asdfx\LaravelGedcom\Concerns;

use Asdfx\LaravelGedcom\Models\Note;

trait NotableModel
{
    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }
}