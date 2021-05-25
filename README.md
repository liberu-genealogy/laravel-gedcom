# GEDCOM to Laravel Model
 ![Latest Stable Version](https://img.shields.io/github/release/genealogiawebsite/laravel-gedcom.svg) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/genealogiawebsite/laravel-gedcom/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/genealogiawebsite/laravel-gedcom/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/genealogiawebsite/laravel-gedcom/badges/build.png?b=master)](https://scrutinizer-ci.com/g/genealogiawebsite/laravel-gedcom/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/genealogiawebsite/laravel-gedcom/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![StyleCI](https://github.styleci.io/repos/268533904/shield?branch=master)](https://github.styleci.io/repos/268533904)
[![CodeFactor](https://www.codefactor.io/repository/github/genealogiawebsite/laravel-gedcom/badge/master)](https://www.codefactor.io/repository/github/genealogiawebsite/laravel-gedcom/overview/master)
[![codebeat badge](https://codebeat.co/badges/911f9e33-212a-4dfa-a860-751cdbbacff7)](https://codebeat.co/projects/github-com-modulargenealogy-gedcom-laravel-gedcom-master)
[![Build Status](https://travis-ci.org/genealogiawebsite/laravel-gedcom.svg?branch=master)](https://travis-ci.org/genealogiawebsite/laravel-gedcom)


familytree365/laravel-gedcom is a package to parse [GEDCOM](https://en.wikipedia.org/wiki/GEDCOM) files, and import them 
as Laravel models, inside your Laravel application. It is used by:
(https://github.com/familytree365/backend)

## Installation
```
composer require familytree365/laravel-gedcom
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



## License

MIT License (see License.md). This means you must retain the copyright and permission notice is all copies, or 
substantial portions of this software. 
