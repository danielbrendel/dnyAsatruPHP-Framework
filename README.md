# Asatru PHP

![https://github.com/danielbrendel/dnyAsatruPHP-Framework/blob/master/LICENSE.txt](https://img.shields.io/github/license/danielbrendel/dnyAsatruPHP-Framework)
![](https://img.shields.io/github/repo-size/danielbrendel/dnyAsatruPHP-Framework)
![https://packagist.org/packages/danielbrendel/asatru-php-framework](https://img.shields.io/packagist/dm/danielbrendel/asatru-php-framework)
![](https://img.shields.io/github/last-commit/danielbrendel/dnyAsatruPHP-Framework)

(C) 2019 - 2020 by Daniel Brendel

**Version**: 0.1\
**Codename**: dnyAsatruPHP\
**Contact**: dbrendel1988 at yahoo com\
**GitHub**: [GitHub](https://github.com/danielbrendel)\
**License**: see LICENSE.txt

## Description:
This product is a lightweight PHP framework which can be used to create your own PHP apps using MVC design pattern.

## Feature overview:
+ Controllers
+ Views
+ Models
+ Migrations
+ Templating
+ Logging
+ .env parser
+ Localization
+ Exception handling
+ Events
+ Validators
+ Helpers
+ Autoloading
+ Security
+ Flash messages
+ Authentication
+ Testing
+ CLI interface
+ mail() wrapper

## Installation
The installation of this framework is just one composer command away:
+ composer require danielbrendel/asatru-php-framework

To create a new project run the command:
+ composer create-project danielbrendel/asatru-php

## Documentation
The documentation is located in the /doc directory consisting of a PDF and a LibreOffice document.

## Testing
In order to run the framework tests you have to place the project folder so as
it would be done with Composer with an App skeleton. This is due to the fact
that the tests use the app skeleton for several input sources. Also be sure that
a database (for testing it is MySQL) is running and adjust the settings.

## Requirements
+ PHP >= 7.3.8
+ PHP PDO extension
+ For databases either MySQL, SQLite, MS SQL Server or Oracle

## Changelog:
+ Version 0.1:
	- (Initial release)