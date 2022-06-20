<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2022 by Daniel Brendel
    
    Version: 1.0
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace Asatru\Config {
    /**
     * This class handles the config management
     */
    class ConfigManager {
        private $path = '';

        /**
         * Construct object
         * 
         * @param string $path The absolute path to the config folder
         * @return void
         */
        public function __construct($path)
        {
            $this->path = $path;
        }

        /**
         * Query config data
         * 
         * @param string $config The relative path to the config file
         * @return array
         */
        public function query($config)
        {
            $content = require($this->path . '/' . $config . '.php');

            return $content;
        }
    }
}

namespace {
    $objConfigManager = new \Asatru\Config\ConfigManager(ASATRU_APP_ROOT . '/app/config');

    /**
     * Query data of a config file
     * @param string $name The name of the config file
     * @return array
     */
    function config($name)
    {
        global $objConfigManager;

        return $objConfigManager->query($name);
    }
}