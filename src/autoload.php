<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    License: see LICENSE.txt
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
    public function __construct($config = __DIR__ . '/../../../../app/config/autoload.php')
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
            if (file_exists(__DIR__ . '/../../app' . $item)) {
                include_once __DIR__ . '/../../app' . $item;
            }
        }
    }
}