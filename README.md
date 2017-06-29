# PHP LSP Test Client

## Setup

```bash
$ composer install
```

You should see the normal composer install process followed by a lot of "Parsing file {name}" output. If you do not see the parsing stuff, also run this:

```bash
$ composer run-script --working-dir=vendor/felixfbecker/language-server parse-stubs
```

### Don't have composer?
[Installation instructions](https://getcomposer.org/download/)

## Run

```
$ php run.php
```

## Requirements

PHP 7.0+