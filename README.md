# Mtr
[![Build Status](https://travis-ci.org/untoreh/mtr.svg?branch=master)](https://travis-ci.org/untoreh/mtr)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/untoreh/mtr/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/untoreh/mtr/?branch=master)
[![Dependency Status](https://gemnasium.com/badges/github.com/untoreh/mtr.svg)](https://gemnasium.com/github.com/untoreh/mtr)

Multi language translator api wrapper in PHP, translate or compare strings or arrays of strings with language pairs supported by multiple [services](/src/services).

## Install 
###### Dependencies
  - APC USer Cache
 
Using composer `composer require untoreh/mtr` on the command line, then include the autoload file in PHP
```php
require "../vendor/autoload.php";

use Mtr\Mtr;
```

## Usage

Pass source/target language and a string or array of strings
```php
$mtr = new Mtr();

$mtr->tr('en', 'fr', 'the fox hides quickly');
// returns : Le renard se cache rapidement

$mtr->tr('en', 'fr', ['the fox', 'hides quickly']);
// returns : ['le renard', 'Se cache rapidement']
```
Array keys are __preserved__.

List of base usable language codes, the priority is to google codes which means if you want 
to translate chinese you should use `zh-TW` or `zh-CN`
```php
$mtr->supLangs();
// returns : [ 'en', 'fr', ... ]
```

Choose which services to use

```php
$mtr->tr('en', 'fr', 'the fox hides quickly', ['google', 'bing']);
```

Add a weight to it to specify how many times a service should be chosen over the others
```php
$mtr->tr('en', 'fr', 'the fox hides quickly', ['google' => 50, 'bing' => 5]);
```

Custom http options 
```php 
$mtr = new Mtr(['request' => ['handler' => $stack]]);
```

Api keys 
```php
$mtr = new Mtr(['systran' => ['key' => $key]);
```

## Conventions
- It is _recommended_ to **not** rely on the translation to return _consistent punctuation_, 
therefore input text should be as __atomic__ as possible.
- Some services arbitrarily encode/decode html or even add html tags themselves, such 
aggressive services have active decoding before the output.

## Notes
- Requests are limited to `1000~` __chars__, strings and arrays get _split or merged_ up to this
size to try to make uniform requests. 
- All the parts of a request are run __concurrently__, _pools_ are not used (yet).
- Default services `weight` is `30` for _google, bing, yandex_ and 10 for the rest.
- Cached keys start with `mtr_`
- Because services may be fickle, they will be dropped as they go down or block access.
- Not all services supports all the languages, the group of services used is transparently trimmed to the ones that support the requested language pair.

## TODO/Improvements
In the [issues](https://github.com/untoreh/mtr/issues)

## Credits
- [guzzle/guzzle](https://github.com/guzzle/guzzle) - _http client_
- [Stichoza/google-translate-php](https://github.com/Stichoza/google-translate-php) - _google token generator_
- [leodido/langcode-conv](https://github.com/leodido/langcode-conv) - _base for the language code converter_
- [joecampo/random-user-agent](https://github.com/joecampo/random-user-agent) - _user-agent strings_

