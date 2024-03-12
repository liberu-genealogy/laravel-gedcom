<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use FamilyTree365\LaravelGedcom\Events\GedComProgressSent;

class ProgressReporter
{
    protected $progressBar;
    protected $totalSteps;
    protected $currentProgress = 0;
    protected $channel;

    public function __construct(int $totalSteps, array $channel)
    {
        $this->totalSteps = $totalSteps;
        $this->channel = $channel;
        $this->initializeProgressBar();
    }

    protected function initializeProgressBar()
    {
        $outputStyle = new OutputStyle(new StringInput(''), new StreamOutput(fopen('php://stdout', 'w')));
        $this->progressBar = $outputStyle->createProgressBar($this->totalSteps);
    }

    public function sendProgressEvent()
    {
        event(new GedComProgressSent($this->channel['name'], $this->totalSteps, $this->currentProgress, $this->channel));
    }

    public function advanceProgress(int $step = 1)
    {
        $this->currentProgress += $step;
        $this->progressBar->advance($step);
        $this->sendProgressEvent();
    }

    public function completeProgress()
    {
        $this->progressBar->finish();
        $this->currentProgress = $this->totalSteps;
        $this->sendProgressEvent();
    }
}
