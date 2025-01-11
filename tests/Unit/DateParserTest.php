

<?php

namespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Utils\DateParser;
use PHPUnit\Framework\TestCase;

class DateParserTest extends TestCase
{
    private DateParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new DateParser();
    }

    /**
     * @dataProvider dateFormatsProvider
     */
    public function testParseDifferentDateFormats(
        string|array|null $dateString, 
        array $expected
    ): void {
        $parser = new DateParser(date_string: $dateString);
        $result = $parser->parse_date();
        
        $this->assertEquals($expected, $result);
    }

    public static function dateFormatsProvider(): array
    {
        return [
            'full date' => [
                '12 JAN 1900',
                ['year' => 1900, 'month' => 1, 'day' => 12]
            ],
            'month year' => [
                'JAN 1900',
                ['year' => 1900, 'month' => 1, 'day' => null]
            ],
            'year only' => [
                '1900',
                ['year' => 1900, 'month' => null, 'day' => null]
            ]
        ];
    }
}