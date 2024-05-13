<?php

/**
 * Handles the importation of data from a GEDCOM file into the Laravel application.
 * This command allows users to specify the filename of the GEDCOM file to be imported.
 */

namespace FamilyTree365\LaravelGedcom\Commands;

use FamilyTree365\LaravelGedcom\Facades\GedcomParserFacade;
use Illuminate\Console\Command;

class GedcomImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gedcom:import {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pass the filename of a GEDCOM file to this command, to have it parsed and imported into a Laravel application.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command to import data from a GEDCOM file.
     *
     * @return int Returns 0 on success, or an error code on failure.
     */
    public function handle(): int
    {
        $filename = $this->argument('filename');
        GedcomParserFacade::parse('mysql', $filename, true, true);
        return 0;
    }
}
