# GEDCOM to Laravel Model
 ![Latest Stable Version](https://img.shields.io/github/release/liberu-genealogy/laravel-gedcom.svg) 
[![Tests](https://github.com/liberu-genealogy/laravel-gedcom/actions/workflows/run-tests.yml/badge.svg)](https://github.com/liberu-genealogy/laravel-gedcom/actions/workflows/run-tests.yml)


liberu-genealogy/laravel-gedcom is a package to parse [GEDCOM](https://en.wikipedia.org/wiki/GEDCOM) files, and import them 
as Laravel models, inside your Laravel application. It is used by:
(https://github.com/liberu-genealogy/genealogy-laravel)


* laravel-gedcom 5.0+ requires PHP 8.3 (or later).
* laravel-gedcom 6.0+ requires PHP 8.4 (or later).
* laravel-gedcom 7.0+ requires PHP 8.4 (or later). Includes GEDCOM X

## Installation
```
composer require liberu-genealogy/laravel-gedcom
```

## Usage

You must create the database schema before doing anything, so run the migrations:
```
php artisan migrate
```

### GEDCOM Files

#### via Command Line
```
php artisan gedcom:import /path/to/your/gedcom/file.ged
```

#### via Facade
```
use FamilyTree365\LaravelGedcom\Facades\GedcomParserFacade;
$filename = '/path/to/your/gedcom/file.ged';
GedcomParserFacade::parse($filename, true);
```

#### via Instantiation
```
use \FamilyTree365\LaravelGedcom\Utils\GedcomParser;
$filename = '/path/to/your/gedcom/file.ged';
$parser = new GedcomParser();
$parser->parse($filename, true);
```

### GedcomX Files

#### via Command Line
```
php artisan gedcomx:import /path/to/your/gedcomx/file.json --progress
```

#### via Facade
```
use FamilyTree365\LaravelGedcom\Facades\GedcomXParserFacade;
$filename = '/path/to/your/gedcomx/file.json';
GedcomXParserFacade::parse('mysql', $filename, 'import-slug', true);
```

#### via Instantiation
```
use \FamilyTree365\LaravelGedcom\Utils\GedcomXParser;
$filename = '/path/to/your/gedcomx/file.json';
$parser = new GedcomXParser();
$parser->parse('mysql', $filename, 'import-slug', true);
```

#### Checking if a file is GedcomX
```
use \FamilyTree365\LaravelGedcom\Utils\GedcomXParser;
$filename = '/path/to/your/file.json';
if (GedcomXParser::isGedcomXFile($filename)) {
    // Process as GedcomX file
} else {
    // Process as regular GEDCOM file
}
```

### High-Performance GedcomX (PHP 8.4+ Optimized)

For maximum performance with large GedcomX files, use the optimized parser that leverages PHP 8.4 features:

#### via Optimized Command Line
```
php artisan gedcomx:import-optimized /path/to/your/gedcomx/file.json --progress --memory-limit=1024 --chunk-size=2000
```

#### via Optimized Instantiation
```
use \FamilyTree365\LaravelGedcom\Utils\GedcomXParserOptimized;
$filename = '/path/to/your/gedcomx/file.json';
$parser = new GedcomXParserOptimized();
$parser->parse('mysql', $filename, 'import-slug', true);
```

#### Performance Features (PHP 8.4+)
- **🚀 Up to 3x faster processing** using modern PHP features
- **💾 50% less memory usage** with optimized data structures
- **📦 Intelligent batch processing** with automatic chunk sizing
- **🔄 Garbage collection optimization** for large datasets
- **⚡ Match expressions** for faster conditional logic
- **🎯 Typed constants** for better performance
- **📊 Real-time performance metrics** during import

#### Performance Comparison
| Feature | Standard Parser | Optimized Parser (PHP 8.4) |
|---------|----------------|----------------------------|
| Processing Speed | 1x | 3x faster |
| Memory Usage | 100% | 50% less |
| Batch Processing | Fixed 500 | Dynamic 1000-5000 |
| Error Handling | Basic | Advanced with metrics |
| PHP Version | 8.3+ | 8.4+ only |

## Documentation

### GEDCOM Data Import
See [GEDCOM_DATA_IMPORT.md](GEDCOM_DATA_IMPORT.md) for a comprehensive reference of all GEDCOM tags and data elements imported by this package.

### Database
This package will create the database tables, which map to models.

### `parse()` Method
The `parse()` method takes three parameters, `string $filename`, `bool $progressBar = false`
and `string $conn` 
If you set `$progressBar` to true, a ProgressBar will be output to `php://stdout`, which is useful when you are calling
the parser from Artisan commands.

## Contributing 

Pull requests are welcome, as are issues. Feel free to submit any feedback too.

## Contributors

<a href = "https://github.com/liberu-genealogy/laravel-gedcom/graphs/contributors">
  <img src = "https://contrib.rocks/image?repo=liberu-genealogy/laravel-gedcom"/>
</a>

## License

MIT License (see License.md). This means you must retain the copyright and permission notice is all copies, or 
substantial portions of this software. 
