<?php

namespace GenealogiaWebsite\LaravelGedcom\Utils\Importer;

use App\Repository;

class RepoRef
{
    /**
     * PhpGedcom\Record\RepoRef $reporef
     * String $group
     * Integer $group_id.
     */
    public static function read($conn, \PhpGedcom\Record\RepoRef $reporef, $group = '', $group_id = 0)
    {
        if ($reporef == null) {
            return;
        }
        $repo = $reporef->getRepo();
        // store Source
        $key = ['group'=>$group, 'gid'=>$group_id, 'repo' => $repo];
        $data = [
            'group'=> $group,
            'gid'  => $group_id,
            'repo' => $repo,
        ];
        $record = Repository::on($conn)->updateOrCreate($key, $data);

        $_group = 'reporef';
        $_gid = $record->id;
        // store Note
        $notes = $reporef->getNote();
        if ($notes && count($notes) > 0) {
            foreach ($notes as $item) {
                NoteRef::read($conn, $item, $_group, $_gid);
            }
        }

        // store Caln
        $caln = $reporef->getCaln();
        if ($caln && count($caln) > 0) {
            foreach ($caln as $item) {
                if ($item) {
                    Caln::read($conn, $item, $_group, $_gid);
                }
            }
        }
    }
}
