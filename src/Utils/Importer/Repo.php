<?php

namespace FamilyTree365\LaravelGedcom\Utils\Importer;

use FamilyTree365\LaravelGedcom\Models\Repository;

readonly class Repo
{
    /**
     * Gedcom\Record\Repo $repo
     * String $group
     * Integer $group_id.
     */
    public static function read(
        string $conn,
        ?\Gedcom\Record\Repo $repo,
        string $group = '',
        int $groupId = 0
    ): ?int {
        if (!$repo) {
            return null;
        }
        $name = $repo->getName(); // string
        $rin = $repo->getRin(); // string
        $addr = $repo->getAddr(); // Record/Addr
        $addr_id = \FamilyTree365\LaravelGedcom\Utils\Importer\Addr::read($conn, $addr);
        $_phon = $repo->getPhon(); // Record/Phon array
        $phon = implode(',', $_phon ?? []);
        $_email = $repo->getEmail();
        $email = implode(',', $_email ?? []);
        $_fax = $repo->getFax();
        $fax = implode(',', $_fax ?? []);
        $_www = $repo->getWww();
        $www = implode(',', $_www ?? []);
        // store Source
        $key = [
            'group'   => $group,
            'gid'     => $groupId,
            'name'    => $name,
            'rin'     => $rin,
            'addr_id' => $addr_id,
            'phon'    => $phon,
            'email'   => $email,
            'fax'     => $fax,
            'www'     => $www,
        ];
        $data = [
            'group'   => $group,
            'gid'     => $groupId,
            'name'    => $name,
            'rin'     => $rin,
            'addr_id' => $addr_id,
            'phon'    => $phon,
            'email'   => $email,
            'fax'     => $fax,
            'www'     => $www,
        ];

        $repository = app(Repository::class)->on($conn)->updateOrCreate($key, $data);

        $_group = 'repo';
        $_gid = $repository->id;
        // store Note
        $note = $repo->getNote(); // Record/NoteRef array
        foreach ($note as $item) {
            NoteRef::read($conn, $item, $_group, $_gid);
        }
        $refn = $repo->getRefn(); // Record/Refn array
        foreach ($refn as $item) {
            Refn::read($conn, $item, $_group, $_gid);
        }

        $chan = $repo->getChan(); // Recore/Chan
        if ($chan instanceof \Gedcom\Record\Chan) {
            \FamilyTree365\LaravelGedcom\Utils\Importer\Chan::read($conn, $chan, $_group, $_gid);
        }

        self::processRelatedRecords($conn, $repo, $_gid);

        return $_gid;
    }

    private static function getKeyAttributes(object $repo, string $group, int $groupId): array
    {
        return [
            'group'   => $group,
            'gid'     => $groupId,
            'name'    => $repo->getName(),
            'rin'     => $repo->getRin(),
        ];
    }

    private static function getRepositoryData(object $repo, string $group, int $groupId): array
    {
        return [
            'group' => $group,
            'gid' => $groupId,
            'name' => $repo->getName(),
            'addr_id' => self::processAddress($repo->getAddr()),
            'phon' => implode(',', $repo->getPhon() ?? []),
            'email' => implode(',', $repo->getEmail() ?? []),
            'fax' => implode(',', $repo->getFax() ?? []),
            'www' => implode(',', $repo->getWww() ?? []),
            'rin' => $repo->getRin(),
        ];
    }

    private static function processRelatedRecords(string $conn, object $repo, int $repositoryId): void
    {
        $note = $repo->getNote(); // Record/NoteRef array
        foreach ($note as $item) {
            NoteRef::read($conn, $item, 'repo', $repositoryId);
        }
        $refn = $repo->getRefn(); // Record/Refn array
        foreach ($refn as $item) {
            Refn::read($conn, $item, 'repo', $repositoryId);
        }

        $chan = $repo->getChan(); // Recore/Chan
        if ($chan instanceof \Gedcom\Record\Chan) {
            \FamilyTree365\LaravelGedcom\Utils\Importer\Chan::read($conn, $chan, 'repo', $repositoryId);
        }
    }
}