<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2025 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

//Logging types
namespace {
    defined('ASATRU_LOG_HEADER') || define('ASATRU_LOG_HEADER', 0);
    defined('ASATRU_LOG_INFO') || define('ASATRU_LOG_INFO', 1);
    defined('ASATRU_LOG_DEBUG') || define('ASATRU_LOG_DEBUG', 2);
    defined('ASATRU_LOG_WARNING') || define('ASATRU_LOG_WARNING', 3);
    defined('ASATRU_LOG_ERROR') || define('ASATRU_LOG_ERROR', 4);
}

namespace Asatru\Logger {
    /**
     * This components handles the logging
     */
    class Logger {
        private $log = array();

        /**
         * Instantiate and add initial item
         * 
         * @return void
         */
        public function __construct()
        {
            $item = ['type' => ASATRU_LOG_HEADER, 'text' => 'Asatru PHP log on ' . date('o-m-j h:i:s a')];
            array_push($this->log, $item);
        }

        /**
         * Get log type string
         * 
         * @param int $logtype
         * @return string
         */
        private function getStrType($logtype)
        {
            switch ($logtype) {
                case ASATRU_LOG_HEADER:
                    return 'Header';
                case ASATRU_LOG_INFO:
                    return 'Info';
                case ASATRU_LOG_DEBUG:
                    return 'Debug';
                case ASATRU_LOG_WARNING:
                    return 'Warning';
                case ASATRU_LOG_ERROR:
                    return 'Error';
                default:
                    return 'Unknown';
            }
        }

        /**
         * Determine whether logging is enabled or not
         * 
         * @return bool
         */
        public function isLoggingEnabled()
        {
            return ((isset($_ENV['LOG_ENABLE'])) && ($_ENV['LOG_ENABLE']));
        }

        /**
         * Add new log line
         * 
         * @param int $type The log event type
         * @param string $line The event message
         * @return void
         */
        public function add($type, $line)
        {
            if (!$this->isLoggingEnabled()) {
                return;
            }

            $item = ['type' => $type, 'text' => $line];
            array_push($this->log, $item);
        }

        /**
         * Clear log content and add initial next item
         * 
         * @return void
         */
        public function clear()
        {
            $this->log = array();

            $item = ['type' => ASATRU_LOG_HEADER, 'text' => 'Asatru PHP log on ' . date('o-m-j h:i:s a')];
            array_push($this->log, $item);
        }

        /**
         * Store log content to file
         * 
         * @return void
         */
        public function store()
        {
            if (!$this->isLoggingEnabled()) {
                return;
            }
            
            $entireStr = '';

            foreach ($this->log as $item) {
                $entireStr .= '[' . date('o-m-j h:i:s a') . '][' . $this->getStrType($item['type']) . '] ' . $item['text'] . PHP_EOL;
            }

            $entireStr .= PHP_EOL . PHP_EOL;

            if (!is_dir(ASATRU_APP_ROOT . '/app/logs')) {
                mkdir(ASATRU_APP_ROOT . '/app/logs');
            }

            file_put_contents(ASATRU_APP_ROOT . '/app/logs/logfile_' . date('o-m-j') . '.txt', $entireStr, FILE_APPEND);

            $this->clear();
        }
    }
}

namespace {
    //Create logging interface functions

    $objLogger = new Asatru\Logger\Logger();

    if (!function_exists('addLog')) {
        /**
         * Add new log line
         * 
         * @param int $type The log event type
         * @param string $line The event message
         * @return void
         */
        function addLog($type, $line)
        {
            global $objLogger;
            $objLogger->add($type, $line);
        }
    }

    if (!function_exists('clearLog')) {
        /**
         * Clear log content and add initial next item
         * 
         * @return void
         */
        function clearLog()
        {
            global $objLogger;
            $objLogger->clear();
        }
    }

    if (!function_exists('storeLog')) {
        /**
         * Store log content to file
         * 
         * @return void
         */
        function storeLog()
        {
            global $objLogger;
            $objLogger->store();
        }
    }
}