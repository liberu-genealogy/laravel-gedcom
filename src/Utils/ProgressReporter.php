<?php

namespace FamilyTree365\LaravelGedcom\Utils;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use FamilyTree365\LaravelGedcom\Events\GedComProgressSent;

/**
 * ProgressReporter class is responsible for reporting the progress of GEDCOM file processing.
 * It manages progress tracking and emits events to update on the current progress.
 */
class ProgressReporter
{
    protected $progressBar;
    protected $totalSteps;
    protected $currentProgress = 0;
    protected $channel;

    /**
     * Constructs a ProgressReporter instance.
     * 
     * @param int $totalSteps The total number of steps to complete.
     * @param array $channel Information about the progress reporting channel.
     */
    public function __construct(int $totalSteps, array $channel)
    {
        $this->totalSteps = $totalSteps;
        $this->channel = $channel;
        $this->initializeProgressBar();
    }

    /**
     * Initializes the progress bar for tracking progress.
     * 
     * This method sets up the progress bar with the total number of steps.
     */
    protected function initializeProgressBar()
    {
        $outputStyle = new OutputStyle(new StringInput(''), new StreamOutput(fopen('php://stdout', 'w')));
        $this->progressBar = $outputStyle->createProgressBar($this->totalSteps);
    }

    /**
     * Sends a progress event to notify about the current progress.
     * 
     * This method emits a GedComProgressSent event with the current progress information.
     */
    public function sendProgressEvent()
    {
        event(new GedComProgressSent($this->channel['name'], $this->totalSteps, $this->currentProgress, $this->channel));
    }

    /**
     * Advances the progress by a specified number of steps.
     * 
     * @param int $step The number of steps to advance the progress by. Defaults to 1.
     */
    public function advanceProgress(int $step = 1)
    {
        $this->currentProgress += $step;
        $this->progressBar->advance($step);
        $this->sendProgressEvent();
    }

    /**
     * Marks the progress as complete.
     * 
     * This method finishes the progress bar and sends a final progress event.
     */
    public function completeProgress()
    {
        $this->progressBar->finish();
        $this->currentProgress = $this->totalSteps;
        $this->sendProgressEvent();
    }
}
    /**
     * Sends a progress event to notify about the current progress.
     * 
     * This method emits a GedComProgressSent event with the current progress information.
     */
    /**
     * Advances the progress by a specified number of steps.
     * 
     * @param int $step The number of steps to advance the progress by. Defaults to 1.
     */
    /**
     * Marks the progress as complete.
     * 
     * This method finishes the progress bar and sends a final progress event.
     */
