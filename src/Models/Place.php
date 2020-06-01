<?php

namespace Asdfx\LaravelGedcom\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Place extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'places';

    protected $fillable = ['title'];

    public static function getIdByTitle($title)
    {
        $place_id = 0;
        if ($title) {
            $place = Place::where('title', '=', $title)->first();
            if (!$place) {
                $place = Place::create(compact('title'));
            }
            $place_id = $place->id;
        }
        return $place_id;
    }
}