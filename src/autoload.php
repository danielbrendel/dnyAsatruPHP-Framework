<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2024 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace Asatru\Autoload;

/**
 * This components handles the autoloading
 */
class Autoloader {
    private $data = [];

    /**
     * Instantiate object
     * 
     * @param string $config optional The absolute path to the config file
     */
    public function __construct($config = ASATRU_APP_ROOT . '/app/config/autoload.php')
    {
        //Load config data
        $this->data = require_once($config);
    }

    /**
     * Load all requested scripts
     * 
     * @return void
     */
    public function load()
    {
        foreach ($this->data as $item) {
            if (file_exists(ASATRU_APP_ROOT . '/app' . $item)) {
                include_once ASATRU_APP_ROOT . '/app' . $item;
            }
        }
    }
}