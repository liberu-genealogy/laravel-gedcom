<?php

namespace ModularSoftware\LaravelGedcom\Utils\Importer;
use \App\Repository;
use \ModularSoftware\LaravelGedcom\Utils\Importer\NoteRef;
use \ModularSoftware\LaravelGedcom\Utils\Importer\Caln;

class RepoRef
{
    /**
     * PhpGedcom\Record\RepoRef $reporef
     * String $group 
     * Integer $group_id
     * 
     */

    public static function read(\PhpGedcom\Record\RepoRef $reporef, $group='', $group_id=0)
    {
        if($reporef == null) {
            return;
        }
        $repo = $reporef->getRepo();
        // store Source
        $key = ['group'=>$group,'gid'=>$group_id, 'repo' => $repo];
        $data = [
            'group'=>$group,
            'gid'=>$group_id,
            'repo'=>$repo,
        ];
        $record = Repository::updateOrCreate($key, $data);

        $_group = 'reporef';
        $_gid = $record->id;
        // store Note
        $notes = $reporef->getNote();
        if($notes && count($notes) > 0) { 
            foreach($notes as $item) { 
                NoteRef::read($item, $_group, $_gid);
            }
        }

        // store Caln
        $caln = $reporef->getData();
        if($caln && count($caln) > 0) {
            foreach($caln as $item) {
                if($item) {
                    Caln::read($item, $_group, $_gid);
                }
            }
        }

        return;
    }
}
