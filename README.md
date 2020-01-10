![License](https://img.shields.io/badge/license-GPLv2-brightgreen "License")
![License](https://img.shields.io/static/v1?label=PHP&message=7.2|7.3&color=7378ae
            "Version")
# lzutf8-php
PHP Decompressor for [LZ-UTF8](https://github.com/rotemdan/lzutf8.js)

LZ-UTF8 is a string compression library and format. Is an extension to the UTF-8 character encoding, augmenting the [UTF-8](https://en.wikipedia.org/wiki/UTF-8) bytestream with optional compression based the [LZ77](https://en.wikipedia.org/wiki/LZ77_and_LZ78) algorithm.

# Compatibility
Code was tested on PHP ```7.2``` and ```7.3```

PHPUnit 8.5 test requires PHP >= 7.2 

# Getting started

## Set Up

### Via Composer
Include it to your project via ```composer.json``` after adding the folder to your current project.
> This package is not available yet on packagist. So you have to install it manually
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "kny00/lzutf8-php",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "kny00/lzutf8-php": "0.0.1",
        "ext-mbstring": "*"
    }
}
```
Then update composer
```bash
composer update
```

### Via Include
This is a one class script, so you can simply include it to your current project
```php
include "kny00/lzutf8/src/Lzutf8/Lzutf8.php"
```

## Use it

```php
use use Knaya\Lzutf8\Lzutf8 as Lzutf8;

/**
 * Creating a compressed 9 chars string 
 * 2 last bytes contain a codepoint sequence
 * [206, 7]
 * distance should be equal to 7 and length to 14
 */
$compressedArray = [
    65, 66, 67, 68, 69, 70, 71, 206, 7
];
$compressedStr = call_user_func_array(
    "pack", 
    array_merge(array("C*"), $compressedArray)
    );

$class = new Lzutf8();
$decompressed = $class->decompress($compressedStr);
// Decompress outputs 21 UTF-8 chars
```

# License
Copyright (c) 2020, KNY <[kny.contact@gmail.com](mailto:kny.contact@gmail.com)>.