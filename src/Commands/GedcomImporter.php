<?php

namespace ModularSoftware\LaravelGedcom\Commands;

use Illuminate\Console\Command;
use ModularSoftware\LaravelGedcom\Facades\GedcomParserFacade;

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filename = $this->argument('filename');
        GedcomParserFacade::parse('mysql', $filename, true, true);
    }
}
