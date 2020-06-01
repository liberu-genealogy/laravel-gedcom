# GEDCOM to Laravel Model
asdfx/laravel-gedcom is a package to parse [GEDCOM](https://en.wikipedia.org/wiki/GEDCOM) files, and import them 
as Laravel models, inside your Laravel application.

## Installation
```
composer require asdfx/laravel-gedcom
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
use Asdfx\LaravelGedcom\Facades\GedcomParserFacade;
$filename = '/path/to/your/gedcom/file.ged';
GedcomParserFacade::parse($filename, true);
```

### via Instantiation
```
use \Asdfx\LaravelGedcom\Utils\GedcomParser;
$filename = '/path/to/your/gedcom/file.ged';
$parser = new GedcomParser();
$parser->parse($filename, true);
```

## Documentation

### Database
This package will create the following database tables, which map to models:
* places -> `Asdfx\LaravelGedcom\Models\Place`
* persons -> `Asdfx\LaravelGedcom\Models\Person`
* person_events -> `Asdfx\LaravelGedcom\Models\PersonEvent`
* families `Asdfx\LaravelGedcom\Models\Family`
* family_events `Asdfx\LaravelGedcom\Models\FamilyEvents`

### `parse()` Method
The `parse()` method takes two parameters, `string $filename`, and `bool $progressBar = false`. 
If you set `$progressBar` to true, a ProgressBar will be output to `php://stdout`, which is useful when you are calling
the parser from Artisan commands.

## Contributing 

Pull requests are welcome, as are issues.
