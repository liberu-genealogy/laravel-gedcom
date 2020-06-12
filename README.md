# GEDCOM to Laravel Model
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
