# Misc PHP and Laravel helper functions and Classes

[![Latest Version on Packagist](https://img.shields.io/packagist/v/daikazu/misc-helpers.svg?style=flat-square)](https://packagist.org/packages/daikazu/misc-helpers)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/daikazu/misc-helpers/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/daikazu/misc-helpers/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/daikazu/misc-helpers/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/daikazu/misc-helpers/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/daikazu/misc-helpers.svg?style=flat-square)](https://packagist.org/packages/daikazu/misc-helpers)

This package provides a collection of helper functions and classes for PHP and Laravel.

## Installation

You can install the package via composer:

```bash
composer require daikazu/misc-helpers
```

## Usage

### Helper Functions

#### format_business_hours
Formats business hours into a human-readable string.

```php
$hours = [
    'monday' => ['open' => '09:00', 'close' => '17:00'],
    'tuesday' => ['open' => '09:00', 'close' => '17:00'],
    'wednesday' => ['open' => '09:00', 'close' => '17:00']
];

$formatted = format_business_hours($hours, 'America/New_York');
echo $formatted; // Outputs: Monday - Wednesday 9am-5pm EDT
```

#### calculate_read_time
Calculates the estimated read time for a given HTML content.
```php
$content = '<p>This is a very short text.</p>';
$readTime = calculate_read_time($content);
echo $readTime; // Outputs: 1
```

#### is_blade_section_empty
Checks if a Blade section is empty.


```bladehtml
@section('test')
    <p>Section is empty</p>
@endsection
```


```php
$isEmpty = is_blade_section_empty('test');
var_dump($isEmpty); // Outputs: bool(false)
```


#### generate_initials
Generates initials from a given name.

```php
$name = 'John Doe';
$initials = generate_initials($name);
echo $initials; // Outputs: JD
```


#### clean_string
Cleans a string by removing extra whitespace and optionally converting it to lowercase.

```php
$string = '   Hello    World   ';
$cleaned = clean_string($string);
echo $cleaned; // Outputs: Hello World
```


## Artisan Commands


## PruneLogFilesCommand

The `PruneLogFilesCommand` is a custom Artisan command used to prune old log files from the storage directory.

### Usage

To run the command, use the following Artisan command:

```bash
php artisan log:prune
```

### Options
- `--days[=DAYS]`: The number of days to retain log files. Files older than this will be deleted. Default is 30 days.

### Example
To prune log files older than 15 days, run:
```bash
php artisan log:prune --days=15
```

## Utility Classes

### PhpArrayFormatter
The PhpArrayFormatter class provides functionality to format PHP arrays into a readable string representation.  

####Usage
To use the PhpArrayFormatter, you can create an instance of the class and call the format method with the array you want to format.
```php
use App\Utilities\PhpArrayFormatter;

$array = [
    'name' => 'John Doe',
    'email' => 'john.doe@example.com',
    'roles' => ['admin', 'user']
];

$formatter = new PhpArrayFormatter();
$formattedArray = $formatter->format($array);

echo $formattedArray;
// Outputs:
// [
//     'name' => 'John Doe',
//     'email' => 'john.doe@example.com',
//     'roles' => [
//         'admin',
//         'user'
//     ]
// ]
```



## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mike Wall](https://github.com/daikazu)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
