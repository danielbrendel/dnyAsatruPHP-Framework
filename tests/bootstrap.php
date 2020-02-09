<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

//If composer is installed we utilize its autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

//Fetch constants
require_once __DIR__ . '/../src/constants.php';

//Require the controller component
require_once __DIR__ . '/../src/controller.php';

//Require logging
require_once __DIR__ . '/../src/logger.php';

//Require .env config management
require_once __DIR__ . '/../src/dotenv.php';

//Parse .env file if it exists
if (file_exists(__DIR__ . '/../../../../.env.testing')) {
    env_parse(__DIR__ . '/../../../../.env.testing');
}

//Require autoload component
require_once __DIR__ . '/../src/autoload.php';

//Require helpers
require_once __DIR__ . '/../src/helper.php';

//Require mail wrapper
require_once __DIR__ . '/../src/mailwrapper.php';

//Require testing component
require_once __DIR__ . '/../src/testing.php';

//Enable debug mode error handling
$_ENV['APP_DEBUG'] = true;
error_reporting(E_ALL);


//Check if we shall create/continue a session
if ((isset($_ENV['APP_SESSION'])) && ($_ENV['APP_SESSION'])) {
    if (!session_start()) {
        throw new Exception('Failed to create/continue the session');
    }

    if (!isset($_SESSION['continued'])) { //Check if a new session
        //Create CSRF-token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        //Mark session
        $_SESSION['continued'] = true;
    }
}

//Require localization
require_once __DIR__ . '/../src/locale.php';


//Require database management
require_once __DIR__ . '/../src/database.php';

//Require event manager
require_once __DIR__ . '/../src/events.php';

//Require console
require_once __DIR__ . '/../src/console.php';