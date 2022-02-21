<?php

namespace FamilyTree365\LaravelGedcom\Utils\Exporter\Sour;

use FamilyTree365\LaravelGedcom\Models\SourceRepo;
use FamilyTree365\LaravelGedcom\Utils\Importer\NoteRef;

class Repo
{
    /**
     * Gedcom\Record\Sour\Data $data
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \Gedcom\Record\Sour\Repo $data, $group = '', $group_id = 0)
    {
        $repo_id = $data->getRepo();
        if (empty($repo_id)) {
            $repo_id = rand(1, 10000);
        }
        $_caln = $data->getCaln();
        $caln = '';
        if ($_caln != null && count($_caln) > 0) {
            $temp = [];
            foreach ($_caln as $item) {
                $__caln = $item->getCaln();
                $__medi = $item->getMedi();
                $temp_item = $__caln.':'.$__medi;
                $temp[] = $temp_item;
            }
            $caln = implode(',', $temp);
            unset($temp);
            // example
            // +1 1123 123123:avi,+1 123 123 123:mpg
        }
        // store Data of sources
        $key = ['group'=>$group, 'gid'=>$group_id, 'repo_id'=>$repo_id];
        $_data = ['group'=>$group, 'gid'=>$group_id, 'repo_id'=>$repo_id, 'caln'=>$caln];
        $record = SourceRepo::on($conn)->updateOrCreate($key, $_data);

        $_group = 'sourcerepo';
        $_gid = $record->id;

        // \Gedcom\Record\NoteRef array
        $note = $data->getNote();
        if ($note && count($note) > 0) {
            foreach ($note as $item) {
                NoteRef::read($conn, $item, $_group, $_gid);
            }
        }
    }
}
