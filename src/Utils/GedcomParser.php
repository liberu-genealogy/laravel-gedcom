<?php

namespace ModularSoftware\LaravelGedcom\Utils;

use \App\Family;
use \App\Person;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class GedcomParser
{
    /**
     * Array of persons ID
     * key - old GEDCOM ID
     * value - new autoincrement ID
     * @var string
     */
    protected $persons_id = [];

    public function parse(string $filename, bool $progressBar = false)
    {
        $parser = new \PhpGedcom\Parser();
        $gedcom = @$parser->parse($filename);

        $individuals = $gedcom->getIndi();
        $families = $gedcom->getFam();

        if ($progressBar === true) {
            $bar = $this->getProgressBar(count($individuals) + count($families));
        }

        foreach ($individuals as $individual) {
            $this->getPerson($individual);
            if ($progressBar === true) {
                $bar->advance();
            }
        }

        foreach ($families as $family) {
            $this->getFamily($family);
            if ($progressBar === true) {
                $bar->advance();
            }
        }

        if ($progressBar === true) {
            $bar->finish();
        }
    }

    private function getProgressBar(int $max)
    {
        return (new OutputStyle(
            new StringInput(''),
            new StreamOutput(fopen('php://stdout', 'w'))
        ))->createProgressBar($max);
    }

    private function getDate($input_date)
    {
        return "$input_date";
    }

    private function getPlace($place)
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
        $person = Person::create(compact('name', 'givn', 'surn', 'sex'));
        $this->persons_id[$g_id] = $person->id;

        if ($events !== null) {
            foreach ($events as $event) {
                $date = $this->getDate($event->getDate());
                $place = $this->getPlace($event->getPlac());
                $person->addEvent($event->getType(), $date, $place);
            };
        }

        if ($attr !== null) {
            foreach ($attr as $event) {
                $date = $this->getDate($event->getDate());
                $place = $this->getPlace($event->getPlac());
                if (count($event->getNote()) > 0) {
                    $note = current($event->getNote())->getNote();
                } else {
                    $note = '';
                }
                $person->addEvent($event->getType(), $date, $place, $event->getAttr() . ' ' . $note);
            };
        }
    }

    private function getFamily($family)
    {
        $g_id = $family->getId();
        $husb = $family->getHusb();
        $wife = $family->getWife();
	$description = NULL;
	$type_id = 0;
        $children = $family->getChil();
        $events = $family->getEven();

        $husband_id = (isset($this->persons_id[$husb])) ? $this->persons_id[$husb] : 0;
        $wife_id = (isset($this->persons_id[$wife])) ? $this->persons_id[$wife] : 0;

        $family = Family::create(compact('husband_id', 'wife_id', 'description', 'type_id'));

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
                $date = $this->getDate($event->getDate());
                $place = $this->getPlace($event->getPlac());
                $family->addEvent($event->getType(), $date, $place);
            };
        }
    }
}
