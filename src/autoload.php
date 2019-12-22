<?php
 
/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

namespace Asatru\Autoload;

//This components handles the autoloading
class Autoloader {
    private $data = [];

    public function __construct($config)
    {
        //Instantiate object

        //Load config data
        $this->data = require_once(__DIR__ . '/../../../../app/config/autoload.php');
    }

    public function load()
    {
        //Load all requested scripts

        foreach ($this->data as $item) {
            if (file_exists(__DIR__ . '/../../app' . $item)) {
                include_once __DIR__ . '/../../app' . $item;
            }
        }
    }
}