<![CDATA[
<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Events\GedComProgressSent;
use FamilyTree365\LaravelGedcom\Utils\ProgressReporter;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProgressReporterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    public function testInitialization()
    {
        $totalSteps = 10;
        $channel = ['name' => 'test-channel', 'eventName' => 'testEvent'];
        $progressReporter = new ProgressReporter($totalSteps, $channel);

        $this->assertEquals($totalSteps, $progressReporter->totalSteps);
        $this->assertEquals($channel, $progressReporter->channel);
    }

    public function testAdvanceProgress()
    {
        $totalSteps = 5;
        $channel = ['name' => 'progress-channel', 'eventName' => 'progressEvent'];
        $progressReporter = new ProgressReporter($totalSteps, $channel);

        $progressReporter->advanceProgress(1);

        Event::assertDispatched(GedComProgressSent::class, function ($event) use ($channel) {
            return $event->channel === $channel && $event->currentProgress === 1;
        });
    }

    public function testCompleteProgress()
    {
        $totalSteps = 3;
        $channel = ['name' => 'complete-channel', 'eventName' => 'completeEvent'];
        $progressReporter = new ProgressReporter($totalSteps, $channel);

        $progressReporter->completeProgress();

        Event::assertDispatched(GedComProgressSent::class, function ($event) use ($totalSteps, $channel) {
            return $event->channel === $channel && $event->currentProgress === $totalSteps;
        });
    }
}
]]>
