&lt;?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use FamilyTree365\LaravelGedcom\Utils\Importer\Addr;
use FamilyTree365\LaravelGedcom\Utils\Importer\Caln;
use FamilyTree365\LaravelGedcom\Utils\Importer\Chan;
// Import other classes from Importer directory as needed

class ImporterTest extends TestCase
{
    public function addrProvider()
    {
        return [
            ['1 ADDR 123 Main St', new Addr('123 Main St')],
            ['1 ADDR ', new Addr('')], // Edge case: empty string
            ['1 ADDR', new Addr('')], // Edge case: malformed line
        ];
    }

    /**
     * @dataProvider addrProvider
     */
    public function testAddrParsing($input, $expected)
    {
        $addr = Addr::parse($input);
        $this->assertEquals($expected, $addr);
    }

    public function calnProvider()
    {
        return [
            ['1 CALN 12345', new Caln('12345')],
            ['1 CALN ', new Caln('')], // Edge case: empty string
            ['1 CALN', new Caln('')], // Edge case: malformed line
        ];
    }

    /**
     * @dataProvider calnProvider
     */
    public function testCalnParsing($input, $expected)
    {
        $caln = Caln::parse($input);
        $this->assertEquals($expected, $caln);
    }

    public function chanProvider()
    {
        return [
            ['1 CHAN\n2 DATE 2023-04-01', new Chan('2023-04-01')],
            ['1 CHAN\n2 DATE ', new Chan('')], // Edge case: empty string
            ['1 CHAN', new Chan('')], // Edge case: malformed line
        ];
    }

    /**
     * @dataProvider chanProvider
     */
    public function testChanParsing($input, $expected)
    {
        $chan = Chan::parse($input);
        $this->assertEquals($expected, $chan);
    }

    // Additional test methods for other classes in the Importer directory
}
