<?php

namespace Asdfx\LaravelGedcom\Commands;

use Asdfx\LaravelGedcom\Models\Family;
use Asdfx\LaravelGedcom\Models\Person;
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
    protected $description = 'Command description';

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
        $start_time = time();

        $filename = $this->argument('filename');
        $filesize = filesize($filename);

        $parser = new \PhpGedcom\Parser();
        $gedcom = @$parser->parse($filename);

        $individuals = $gedcom->getIndi();
        $families = $gedcom->getFam();
        $bar = $this->output->createProgressBar(count($individuals) + count($families));

        foreach ($individuals as $individual) {
            $this->getPerson($individual);
            $bar->advance();
        }

        foreach ($families as $family) {
            $this->get_Family($family);
            $bar->advance();
        }
        $bar->finish();

        echo "\n";
        $end_time = time();
        $taken_seconds = $end_time - $start_time;
        if ($taken_seconds > 0) {
            $rate = round($filesize / $taken_seconds, 2);
            echo "$taken_seconds seconds taken to handle $filesize bytes. $rate bytes per second\n";
        }
    }

    private function get_date($input_date)
    {
        return "$input_date";
    }

    private function get_place($place)
    {
        if (is_object($place)) {
            $place = $place->getPlac();
        }
        return $place;
    }

    private function getPerson($individual)
    {
        $g_id = $individual->getId();
        $name = '';
        $givn = '';
        $surn = '';

        if (!empty($individual->getName())) {
            $surn = current($individual->getName())->getSurn();
            $givn = current($individual->getName())->getGivn();
            $name = current($individual->getName())->getName();
        }

        $sex = $individual->getSex();
        $attr = $individual->getAttr();
        $events = $individual->getEven();

        if ($givn == "") {
            $givn = $name;
        }
        $person = Person::create(compact('givn', 'surn', 'sex'));
        $this->persons_id[$g_id] = $person->id;

        if ($events !== null) {
            foreach ($events as $event) {
                $date = $this->get_date($event->getDate());
                $place = $this->get_place($event->getPlac());
                $person->add_event($event->getType(), $date, $place);
            };
        }

        if ($attr !== null) {
            foreach ($attr as $event) {
                $date = $this->get_date($event->getDate());
                $place = $this->get_place($event->getPlac());
                if (count($event->getNote()) > 0) {
                    $note = current($event->getNote())->getNote();
                } else {
                    $note = '';
                }
                $person->add_event($event->getType(), $date, $place, $event->getAttr() . ' ' . $note);
            };
        }
    }

    private function get_Family($family)
    {
        $g_id = $family->getId();
        $husb = $family->getHusb();
        $wife = $family->getWife();
        $children = $family->getChil();
        $events = $family->getEven();
        // echo "$g_id\n";
        $husband_id = (isset($this->persons_id[$husb])) ? $this->persons_id[$husb] : 0;
        $wife_id = (isset($this->persons_id[$wife])) ? $this->persons_id[$wife] : 0;

        $family = Family::create(compact('husband_id', 'wife_id'));

        if ($children !== null) {
            foreach ($children as $child) {
                if (isset($this->persons_id[$child])) {
                    $person = Person::find($this->persons_id[$child]);
                    $person->child_in_family_id = $family->id;
                    $person->save();
                }
            }
        }

        if ($events !== null) {
            foreach ($events as $event) {
                $date = $this->get_date($event->getDate());
                $place = $this->get_place($event->getPlac());
                $family->add_event($event->getType(), $date, $place);
            };
        }
    }
}
