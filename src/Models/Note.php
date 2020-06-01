<?php

namespace Asdfx\LaravelGedcom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Note extends Model
{
    protected $fillable = ['notes'];

    public function notable(): MorphTo
    {
        return $this->morphTo();
    }
}