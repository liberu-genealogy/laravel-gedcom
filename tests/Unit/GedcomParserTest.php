&lt;?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use FamilyTree365\LaravelGedcom\Utils\GedcomParser;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use FamilyTree365\LaravelGedcom\Events\GedComProgressSent;
use Gedcom\Parser as GedcomParserLib;

class GedcomParserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        DB::shouldReceive('disableQueryLog');
    }

    public function gedcomProvider()
    {
        return [
            ['sample.ged', true],
            ['invalid.ged', false]
        ];
    }

    /**
     * @dataProvider gedcomProvider
     */
    public function testParseSuccess($filename, $expectedResult)
    {
        $parserMock = $this->createMock(GedcomParserLib::class);
        $parserMock->method('parse')->willReturn($this->getGedcomStructure($expectedResult));

        $gedcomParser = new GedcomParser();
        $result = $gedcomParser->parse('connection', $filename, 'slug', true, ['name' => 'channelName', 'eventName' => 'event']);

        $this->assertEquals($expectedResult, $result);
        Event::assertDispatched(GedComProgressSent::class);
    }

    public function testParseWithException()
    {
        $parserMock = $this->createMock(GedcomParserLib::class);
        $parserMock->method('parse')->will($this->throwException(new \Exception("Error parsing GEDCOM")));

        $gedcomParser = new GedcomParser();
        Log::shouldReceive('error')->once();

        $this->expectException(\Exception::class);
        $gedcomParser->parse('connection', 'faulty.ged', 'slug', true, ['name' => 'channelName', 'eventName' => 'event']);
    }

    private function getGedcomStructure($valid = true)
    {
        if ($valid) {
            // Return a valid GEDCOM structure
            return new \stdClass(); // Simplified for example purposes
        } else {
            // Return an invalid GEDCOM structure or null
            return null;
        }
    }
}
