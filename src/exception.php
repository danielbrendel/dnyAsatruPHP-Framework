<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace {
    $CatchExceptions = function($e) {
        //Handle all exceptions here

        if ((isset($_ENV['APP_DEBUG'])) && ($_ENV['APP_DEBUG'] === true)) {
            $exception = $e;
            require_once ASATRU_APP_ROOT . '/app/views/error/exception_debug.php';
            unset($exception);
        } else {
            require_once ASATRU_APP_ROOT . '/app/views/error/exception_prod.php';
        }

        if (function_exists('addLog')) {
            addLog(ASATRU_LOG_ERROR, $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        }
    };

    $_ENV['APP_DEBUG'] = true;

    set_exception_handler($CatchExceptions);
}