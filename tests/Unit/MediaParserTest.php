<?php

namespace Tests\Unit;

use Tests\TestCase;
use FamilyTree365\LaravelGedcom\Utils\Importer\MediaParser;
use FamilyTree365\LaravelGedcom\Models\Media;
use Illuminate\Support\Facades\DB;
use Mockery;

class MediaParserTest extends TestCase
{
    protected $mediaParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockDatabase();
        $this->mediaParser = new MediaParser(DB::connection());
    }

    private function mockDatabase()
    {
        DB::shouldReceive('beginTransaction')->andReturnSelf();
        DB::shouldReceive('commit')->andReturnSelf();
        DB::shouldReceive('rollBack')->andReturnSelf();
    }

    public function testParseMediaObjectsWithValidData()
    {
        $mediaObjects = [
            (object)[
                'getFile' => 'test.jpg',
                'getTitle' => 'Test Image',
                'getNote' => 'Test Note'
            ]
        ];

        Media::shouldReceive('create')->once()->andReturn(new Media());
        
        $result = $this->mediaParser->parseMediaObjects($mediaObjects);
        $this->assertTrue($result);
    }
}mespace Tests\Unit;

use FamilyTree365\LaravelGedcom\Utils\Importer\MediaParser;
use FamilyTree365\LaravelGedcom\Models\Media;
use FamilyTree365\LaravelGedcom\Models\Person;
use FamilyTree365\LaravelGedcom\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class MediaParserTest extends TestCase
{
    use RefreshDatabase;

    protected $mediaParser;

    protected function setUp(): void
    {
        parent::setUp();
        DB::shouldReceive('beginTransaction')->andReturnTrue();
        DB::shouldReceive('commit')->andReturnTrue();
        DB::shouldReceive('rollBack')->andReturnTrue();
        $this->mediaParser = new MediaParser(DB::connection());
    }

    public function testParseMediaObjectsWithValidData()
    {
        $mediaObjects = [
            (object)['getFile' => 'path/to/file1.jpg', 'getTitle' => 'Title 1', 'getNote' => 'Note 1', 'getIndiIds' => ['I1'], 'getFamIds' => ['F1']],
            (object)['getFile' => 'path/to/file2.jpg', 'getTitle' => 'Title 2', 'getNote' => 'Note 2', 'getIndiIds' => ['I2'], 'getFamIds' => []]
        ];

        Media::shouldReceive('save')->twice();
        Person::shouldReceive('where')->andReturnSelf();
        Person::shouldReceive('first')->andReturn(new Person());
        Family::shouldReceive('where')->andReturnSelf();
        Family::shouldReceive('first')->andReturn(new Family());

        $this->mediaParser->parseMediaObjects($mediaObjects);

        $this->assertDatabaseHas('media', ['title' => 'Title 1']);
        $this->assertDatabaseHas('media', ['title' => 'Title 2']);
    }

    public function testParseMediaObjectsWithEmptyArray()
    {
        $mediaObjects = [];

        $this->mediaParser->parseMediaObjects($mediaObjects);

        $this->assertTrue(true); // No exception means pass
    }

    public function testParseMediaObjectsWithInvalidData()
    {
        $mediaObjects = ['invalid_data'];

        $this->expectException(\Exception::class);
        $this->mediaParser->parseMediaObjects($mediaObjects);
    }
}