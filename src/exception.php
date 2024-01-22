<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2024 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace {
    $CatchExceptions = function($e) {
        //Handle all exceptions here

        if (!class_exists('Asatru\\View\\ViewHandler')) {
            require_once 'view.php';
        }

        $view = null;

        if ((isset($_ENV['APP_DEBUG'])) && ($_ENV['APP_DEBUG'] === true)) {
            if (ob_get_contents()) {
                ob_clean();
            }
            
            $view = view('error/exception_debug', [], ['exception' => $e]);
        } else {
            $view = view('error/exception_prod', [], ['exception' => $e]);
        }

        $view->out();

        if (function_exists('addLog')) {
            addLog(ASATRU_LOG_ERROR, $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            storeLog();
        }
    };

    $_ENV['APP_DEBUG'] = true;

    set_exception_handler($CatchExceptions);
}