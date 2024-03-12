<?php

namespace FamilyTree365\LaravelGedcom\Commands;

use FamilyTree365\LaravelGedcom\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class GedcomExporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gedcom:export {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export database data into a GEDCOM file format';

    /**
     * Create a new command instance.
     *
     * @return void
     */
use FamilyTree365\LaravelGedcom\Commands\GedcomExporterHelpers;
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $dir = 'public/gedcom/exported';

        $filename = $this->argument('filename').'.GED';

        GedcomExporterHelpers::createDirectory($dir);

        $submissions = GedcomExporterHelpers::fetchDatabaseData();
        $people = Person::all();

        $data = [
        $source = View::make('stubs.ged', $data)->render();
        $gedcomDocument = GedcomExporterHelpers::createGedcomDocumentString($source);
        GedcomExporterHelpers::writeToFile($filename, $gedcomDocument);
    }
}
        return 0;
