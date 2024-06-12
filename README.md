# GEDCOM to Laravel Model
 ![Latest Stable Version](https://img.shields.io/github/release/liberu-genealogy/laravel-gedcom.svg) 
[![Tests](https://github.com/liberu-genealogy/laravel-gedcom/actions/workflows/run-tests.yml/badge.svg)](https://github.com/liberu-genealogy/laravel-gedcom/actions/workflows/run-tests.yml)


liberu-genealogy/laravel-gedcom is a package to parse [GEDCOM](https://en.wikipedia.org/wiki/GEDCOM) files, and import them 
as Laravel models, inside your Laravel application. It is used by:
(https://github.com/liberu-genealogy/genealogy-laravel)

## Installation
```
composer require liberu-genealogy/laravel-gedcom
```

## Usage

You must create the database schema before doing anything, so run the migrations:
```
php artisan migrate
```

### via Command Line
```
php artisan gedcom:import /path/to/your/gedcom/file.ged
```

### via Facade
```
use FamilyTree365\LaravelGedcom\Facades\GedcomParserFacade;
$filename = '/path/to/your/gedcom/file.ged';
GedcomParserFacade::parse($filename, true);
```

### via Instantiation
```
use \FamilyTree365\LaravelGedcom\Utils\GedcomParser;
$filename = '/path/to/your/gedcom/file.ged';
$parser = new GedcomParser();
$parser->parse($filename, true);
```

## Documentation

### Database
This package will create the database tables, which map to models.

### `parse()` Method
The `parse()` method takes three parameters, `string $filename`, `bool $progressBar = false`
and `string $conn` 
If you set `$progressBar` to true, a ProgressBar will be output to `php://stdout`, which is useful when you are calling
the parser from Artisan commands.

## Contributing 

Pull requests are welcome, as are issues.

## Contributors

<a href = "https://github.com/liberu-genealogy/laravel-gedcom/graphs/contributors">
  <img src = "https://contrib.rocks/image?repo=liberu-genealogy/laravel-gedcom"/>
</a>

## License

MIT License (see License.md). This means you must retain the copyright and permission notice is all copies, or 
substantial portions of this software. 
