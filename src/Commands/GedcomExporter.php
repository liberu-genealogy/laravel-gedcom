<?php

/**
 * Handles the exportation of data from the database to a GEDCOM file format.
 * This command allows users to specify a filename for the exported GEDCOM file.
 */

namespace FamilyTree365\LaravelGedcom\Commands;

use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Models\Family;
use FamilyTree365\LaravelGedcom\Models\Subm;
use FamilyTree365\LaravelGedcom\Models\Note;
use FamilyTree365\LaravelGedcom\Models\MediaObject;
use FamilyTree365\LaravelGedcom\Models\Source;
use FamilyTree365\LaravelGedcom\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

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
     * Create a new GedcomExporter command instance.
     */
    use GedcomExporterHelpers;
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command to export data into a GEDCOM file.
     *
     * @return int Returns 0 on success, or an error code on failure.
     */
    public function handle(): int
    {
        try {
            $dir = 'public/gedcom/exported';
            $filename = $this->argument('filename').'.GED';
            $fullPath = storage_path('app/'.$dir.'/'.$filename);

            $this->info('Starting GEDCOM export to: ' . $filename);

            self::createDirectory($dir);

            // Fetch all required data from database
            $submissions = self::fetchDatabaseData();
            $people = Person::with(['events', 'names', 'notes', 'sources', 'media'])->get();
            $families = Family::with(['husband', 'wife', 'children', 'notes', 'sources', 'media'])->get();
            $notes = Note::all();
            $sources = Source::with(['repositories', 'notes'])->get();
            $repositories = Repository::all();
            $mediaObjects = MediaObject::all();

            $data = [
                'submissions' => $submissions,
                'people' => $people,
                'families' => $families,
                'notes' => $notes,
                'sources' => $sources,
                'repositories' => $repositories,
                'mediaObjects' => $mediaObjects,
                'exportDate' => Carbon::now()->format('d M Y'),
                'exportTime' => Carbon::now()->format('H:i:s'),
            ];

            $this->info('Generating GEDCOM content...');
            $source = View::make('stubs.ged', $data)->render();
            $gedcomDocument = self::createGedcomDocumentString($source);

            $this->info('Writing GEDCOM file...');
            self::writeToFile($fullPath, $gedcomDocument);

            $this->info('GEDCOM export completed successfully: ' . $fullPath);
            return 0;
        } catch (\Exception $e) {
            $this->error('An error occurred while exporting the GEDCOM file: ' . $e->getMessage());
            return 1;
        }
    }
}