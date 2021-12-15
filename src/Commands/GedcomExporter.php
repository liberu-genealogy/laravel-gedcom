<?php

namespace FamilyTree365\LaravelGedcom\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Models\Subm;
use Illuminate\Support\Facades\DB;
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

         $dir = 'public/gedcom/exported';

        $filename = $this->argument('filename').".GED";

        $file_path = $dir . '/' . $filename;

        if (!file_exists($dir)) {
            Storage::makeDirectory($dir);
        }

        $query = DB::table('subms');
        $query->join('addrs', 'addrs.id', '=', 'subms.addr_id');
        $query->select([
            'subms.name',
            'addrs.adr1',
            'addrs.adr2',
            'addrs.city',
            'addrs.stae',
            'addrs.post',
            'addrs.ctry',
            'subms.phon',
        ])->get();

        $people = Person::all();
        $submissions = $query->get();

        $data =array (
            'submissions' => $submissions,
            'people' => $people,
        );

        $source = View::make('stubs.ged', $data)->render();

        $ged_doc = "HEAD \nGEDC \nVERS 5.5.5 \nFORM LINEAGE-LINKED \nVERS 5.5.5 \nCHAR UTF-8 \nSOUR GS \nVERS 5.5.5 \nCORP gedcom.org\n";

        $handle = fopen($filename, 'w');

        fputs($handle, $ged_doc.$source);

        fclose($handle);

        $headers = array(
            'Content-Type' => 'text/ged',
        );

    }
}
