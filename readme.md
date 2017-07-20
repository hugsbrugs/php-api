# php-api

This librairy provides utilities function to group different API calls for a single project entry point. Based on [Tonic](https://github.com/peej/tonic).

## Install

Install package with composer
```
composer require hugsbrugs/php-api
```

In your PHP code, load library
```php
require_once __DIR__ . '/../vendor/autoload.php';
use Hug\Api\Api as Api;
```

## Usage

### Edit config.php

Set your parameters. Visit Tonic documentation for params.

### Put dispatch.php and .htaccess files in api folder

If you want to access your API through /api subfolder, create this folder at your webroot directory and copy .htaccess (which will redirect all traffic to dispatch.php) and dispatch.php (which will route your API requests)

### Create your API classes

Look at TestApi.php and create your API endpoints extending 
```php
Hug\Api\ApiResource
```
Based on Tonic syntax

### Consume your API

In your browser visit your endpoints or do CURL requests to test POST, PUT, DELETE methods.


## Author

Hugo Maugey [visit my website ;)](https://hugo.maugey.fr)