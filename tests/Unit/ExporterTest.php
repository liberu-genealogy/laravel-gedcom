&lt;?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use FamilyTree365\LaravelGedcom\Utils\Exporter\Addr;
use FamilyTree365\LaravelGedcom\Utils\Exporter\Caln;
use FamilyTree365\LaravelGedcom\Utils\Exporter\Chan;
// Import other classes from Exporter directory as needed

class ExporterTest extends TestCase
{
    public function addrProvider()
    {
        return [
            ['123 Main St', '1 ADDR 123 Main St'],
            ['', '1 ADDR '], // Edge case: empty string
            [null, '1 ADDR '], // Edge case: null
        ];
    }

    /**
     * @dataProvider addrProvider
     */
    public function testAddr($input, $expected)
    {
        $addr = new Addr($input);
        $this->assertEquals($expected, $addr->toGedcom(null, 1));
    }

    public function calnProvider()
    {
        return [
            ['12345', '1 CALN 12345'],
            ['', '1 CALN '], // Edge case: empty string
            [null, '1 CALN '], // Edge case: null
        ];
    }

    /**
     * @dataProvider calnProvider
     */
    public function testCaln($input, $expected)
    {
        $caln = new Caln($input);
        $this->assertEquals($expected, $caln->toGedcom(null, 1));
    }

    public function chanProvider()
    {
        return [
            ['2023-04-01', '1 CHAN\n2 DATE 2023-04-01'],
            ['', '1 CHAN\n2 DATE '], // Edge case: empty string
            [null, '1 CHAN\n2 DATE '], // Edge case: null
        ];
    }

    /**
     * @dataProvider chanProvider
     */
    public function testChan($input, $expected)
    {
        $chan = new Chan($input);
        $this->assertEquals($expected, $chan->toGedcom(null, 1));
    }

    // Additional test methods for other classes in the Exporter directory
}
