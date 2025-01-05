<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2025 by Daniel Brendel
    
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
         * @param bool $as_obj If config array shall be returned as object
         * @return array|null
         */
        public function query($config, $as_obj = true)
        {
            if (!file_exists($this->path . '/' . $config . '.php')) {
                return null;
            }

            $content = require($this->path . '/' . $config . '.php');

            if ((is_array($content)) && ($as_obj === true)) {
                return json_decode(json_encode($content));
            }

            return $content;
        }
    }
}

namespace {
    $objConfigManager = new \Asatru\Config\ConfigManager(ASATRU_APP_ROOT . '/app/config');

    /**
     * Query data of a config file
     * @param string $name The name of the config file
     * @param bool $as_obj If config array shall be returned as object
     * @return array|null
     */
    function config($name, $as_obj = true)
    {
        global $objConfigManager;

        return $objConfigManager->query($name, $as_obj);
    }
}