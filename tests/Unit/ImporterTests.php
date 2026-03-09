<?php

namespace Tests\Unit;

use Tests\TestCase;
use FamilyTree365\LaravelGedcom\Utils\Importer\Note;
use FamilyTree365\LaravelGedcom\Utils\Importer\Obje;
use FamilyTree365\LaravelGedcom\Utils\Importer\Repo;

class ImporterTests extends TestCase
{
    public function testNoteImporterClassExists()
    {
        $this->assertTrue(class_exists(Note::class));
    }

    public function testNoteImporterHasReadMethod()
    {
        $this->assertTrue(method_exists(Note::class, 'read'));
    }

    public function testObjeImporterClassExists()
    {
        $this->assertTrue(class_exists(Obje::class));
    }

    public function testObjeImporterHasReadMethod()
    {
        $this->assertTrue(method_exists(Obje::class, 'read'));
    }

    public function testRepoImporterClassExists()
    {
        $this->assertTrue(class_exists(Repo::class));
    }

    public function testRepoImporterHasReadMethod()
    {
        $this->assertTrue(method_exists(Repo::class, 'read'));
    }
}