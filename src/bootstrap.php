<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

//Require exception handler
require_once 'exception.php';

//Fetch constants
require_once 'constants.php';

//Require the controller component
require_once 'controller.php';

//Require logging
require_once 'logger.php';

//Require .env config management
require_once 'dotenv.php';

//Require autoload component
require_once 'autoload.php';

//Require helpers
require_once 'helper.php';

//Parse .env file if it exists
if (file_exists(__DIR__ . '/../../../../.env')) {
    env_parse(__DIR__ . '/../../../../.env');
}

//Set error reporting according to debug flag value
if ($_ENV['APP_DEBUG'] === false) {
    error_reporting(0);
} else {
    error_reporting(E_ALL);
}

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
require_once 'locale.php';


//Require database management
require_once 'database.php';

//Require event manager
require_once 'events.php';

//Perform autoloading
$auto = new Asatru\Autoload\Autoloader(__DIR__ . '/../../../../app/config/autoload.php');
$auto->load();

//Load validators if any
Asatru\Controller\CustomPostValidators::load(__DIR__ . '/../../../../app/validators');

//Require mail wrapper
require_once 'mailwrapper.php';

//Create a new controller instance and handle the current URL
$controller = new Asatru\Controller\ControllerHandler(__DIR__ . '/../../../../app/config/routes.php');
$viewComp = $controller->parse($_SERVER['REQUEST_URI']);
HandleView($viewComp);

//Clear flash messages
FlashMessage::clearAll();

//If app is in debug mode then force storage of log
if ($_ENV['APP_DEBUG']) {
    storeLog();
}