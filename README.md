# GEDCOM to Laravel Model
 ![Latest Stable Version](https://img.shields.io/github/release/modularsoftware/laravel-gedcom.svg) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/modularsoftware/laravel-gedcom/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/modularsoftware/laravel-gedcom/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/modularsoftware/laravel-gedcom/badges/build.png?b=master)](https://scrutinizer-ci.com/g/modularsoftware/laravel-gedcom/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/modularsoftware/laravel-gedcom/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![StyleCI](https://github.styleci.io/repos/135390590/shield?branch=master)](https://github.styleci.io/repos/135390590)
[![CodeFactor](https://www.codefactor.io/repository/github/modularsoftware/laravel-gedcom/badge/master)](https://www.codefactor.io/repository/github/modularsoftware/laravel-gedcom/overview/master)
[![codebeat badge](https://codebeat.co/badges/911f9e33-212a-4dfa-a860-751cdbbacff7)](https://codebeat.co/projects/github-com-modulargenealogy-gedcom-laravel-gedcom-master)
[![Build Status](https://travis-ci.org/modularsoftware/laravel-gedcom.svg?branch=master)](https://travis-ci.org/modularsoftware/laravel-gedcom)


modularsoftware/laravel-gedcom is a package to parse [GEDCOM](https://en.wikipedia.org/wiki/GEDCOM) files, and import them 
as Laravel models, inside your Laravel application. It is used by:
(https://github.com/modularsoftware/genealogy)

## Installation
```
composer require modularsoftware/laravel-gedcom
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
use ModularSoftware\LaravelGedcom\Facades\GedcomParserFacade;
$filename = '/path/to/your/gedcom/file.ged';
GedcomParserFacade::parse($filename, true);
```

### via Instantiation
```
use \ModularSoftware\LaravelGedcom\Utils\GedcomParser;
$filename = '/path/to/your/gedcom/file.ged';
$parser = new GedcomParser();
$parser->parse($filename, true);
```

## Documentation

### Database
This package relies on the database tables already in modularsoftware/genealogy
 which map to models:

### `parse()` Method
The `parse()` method takes two parameters, `string $filename`, and `bool $progressBar = false`. 
If you set `$progressBar` to true, a ProgressBar will be output to `php://stdout`, which is useful when you are calling
the parser from Artisan commands.

## Contributing 

Pull requests are welcome, as are issues.


## License

MIT License (see License.md). This means you must retain the copyright and permission notice is all copies, or 
substantial portions of this software. 
