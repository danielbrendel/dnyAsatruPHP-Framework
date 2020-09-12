# Asatru PHP

(C) 2019 - 2020 by Daniel Brendel

**Version**: 1.0\
**Codename**: dnyAsatruPHP\
**Contact**: dbrendel1988(at)gmail(dot)com\
**GitHub**: https://github.com/danielbrendel

Released under the MIT license

## Description:
This product is a lightweight PHP framework which can be used to create your own PHP apps using MVC design pattern.

## Feature overview:
+ Controllers
+ Views
+ Models
+ Modules
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
+ Caching
+ Testing
+ CLI interface
+ mail() wrapper
+ SMTP mailing
+ Carbon support
+ npm/webpack support

## Installation
The installation of this framework is just one composer command away:
+ composer require danielbrendel/asatru-php-framework

To create a new project run the command:
+ composer create-project danielbrendel/asatru-php

## Documentation
The source documentation file is located in the /doc directory. It is a LibreOffice Writer document.

## Testing
In order to run the framework tests you have to place the project folder so as
it would be done with Composer with an App skeleton. This is due to the fact
that the tests use the app skeleton for several input sources and output. Also be 
sure that a database (for testing it is MySQL) is running and adjust the settings.

## Requirements
+ PHP >= 7.4.6
+ PHP PDO extension
+ MySQL when database needed

## Changelog:
+ Version 1.0:
	- (Initial release)