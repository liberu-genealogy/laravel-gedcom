<?php

namespace FamilyTree365\LaravelGedcom\Utils\Exporter;

class Caln
{
    /**
     * Gedcom\Record\Caln $caln
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, $caln, $group = null, $gid = null)
    {
        if ($caln == null || is_array($caln)) {
            return;
        }

        $medi = $addr->getMedi();

        $key = [
            'group'=> $group,
            'gid'  => $gid,
            'medi' => $medi,
        ];
        $data = [
            'group'=> $group,
            'gid'  => $gid,
            'medi' => $medi,
        ];
        app(MCaln::class)->on($conn)->updateOrCreate($key, $data);
    }
}
