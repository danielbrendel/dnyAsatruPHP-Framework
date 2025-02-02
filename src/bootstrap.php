<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2025 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

//Set starting time
define('ASATRU_START', microtime(true));

//Set application root directory path
define('ASATRU_APP_ROOT', __DIR__ . '/../../../..');

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

//Require config component
require_once 'config.php';

//Require helpers
require_once 'helper.php';

//Require Html helper
require_once 'html.php';

//Require form helper
require_once 'forms.php';

//Parse .env file if it exists
if (file_exists(ASATRU_APP_ROOT . '/.env')) {
    env_parse(ASATRU_APP_ROOT . '/.env');
}

//Set timezone if desired
if ((isset($_ENV['APP_TIMEZONE'])) && (is_string($_ENV['APP_TIMEZONE'])) && (strlen($_ENV['APP_TIMEZONE']) > 0)) {
    date_default_timezone_set($_ENV['APP_TIMEZONE']);
}

//Set error reporting according to debug flag value
if ($_ENV['APP_DEBUG'] === false) {
    error_reporting(0);
} else {
    error_reporting(E_ALL);
}

//Create argv if not exists
if (!isset($argv)) {
    $argv = [];
}

//Check if we shall create/continue a session
if ((isset($_ENV['SESSION_ENABLE'])) && ($_ENV['SESSION_ENABLE'])) {
    if ((isset($_ENV['SESSION_DURATION'])) && ($_ENV['SESSION_DURATION']) && (is_numeric($_ENV['SESSION_DURATION']))) {
        ini_set('session.cookie_lifetime', $_ENV['SESSION_DURATION']);
        ini_set('session.gc_maxlifetime', $_ENV['SESSION_DURATION']);
    }

    if ((isset($_ENV['SESSION_NAME'])) && (is_string($_ENV['SESSION_NAME'])) && (strlen($_ENV['SESSION_NAME']) > 0)) {
        session_name($_ENV['SESSION_NAME']);
    }

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

//Require modules
require_once 'modules.php';

//Require event manager
require_once 'events.php';

//Require commands manager
require_once 'commands.php';

//Require console management
require_once 'console.php';

//Perform autoloading
$auto = new Asatru\Autoload\Autoloader(ASATRU_APP_ROOT . '/app/config/autoload.php');
$auto->load();

//Load validators if any
Asatru\Controller\CustomPostValidators::load(ASATRU_APP_ROOT . '/app/validators');

//Require mail wrapper
require_once 'mailwrapper.php';

//Require SMTP mail handler
require_once 'smtpmailer.php';

//Clear old POST data if approbriate
if (!isset($_SESSION['asatru_keep_old_post_data'])) {
    Asatru\Controller\OldPostData::clear();
}

//Create a new controller instance and handle the current URL
$controller = new Asatru\Controller\ControllerHandler(ASATRU_APP_ROOT . '/app/config/routes.php');
$viewComp = $controller->parse($_SERVER['REQUEST_URI']);
HandleView($viewComp);

//Clear flash messages
FlashMessage::clearAll();

//Remove flag if set
if (isset($_SESSION['asatru_keep_old_post_data'])) {
    unset($_SESSION['asatru_keep_old_post_data']);
}

//Attempt to store the current log
storeLog();