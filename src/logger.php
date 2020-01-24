<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

//Logging types
namespace {
    defined('LOG_HEADER') || define('LOG_HEADER', 0);
    defined('LOG_INFO') || define('LOG_HEADER', 1);
    defined('LOG_DEBUG') || define('LOG_HEADER', 2);
    defined('LOG_WARNING') || define('LOG_HEADER', 3);
    defined('LOG_ERROR') || define('LOG_ERROR', 4);
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
            $item = ['type' => LOG_HEADER, 'text' => 'Asatru PHP log on ' . date('o-m-j h:i:s a')];
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
                case LOG_HEADER:
                    return 'Header';
                case LOG_INFO:
                    return 'Info';
                case LOG_DEBUG:
                    return 'Debug';
                case LOG_WARNING:
                    return 'Warning';
                case LOG_ERROR:
                    return 'Error';
                default:
                    return 'Unknown';
            }
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

            $item = ['type' => LOG_HEADER, 'text' => 'Asatru PHP log on ' . date('o-m-j h:i:s a')];
            array_push($this->log, $item);
        }

        /**
         * Store log content to file
         * 
         * @return void
         */
        public function store()
        {
            $entireStr = '';

            foreach ($this->log as $item) {
                $entireStr .= '[' . date('o-m-j h:i:s a') . '][' . $this->getStrType($item['type']) . '] ' . $item['text'] . PHP_EOL;
            }

            $entireStr .= PHP_EOL . PHP_EOL;

            if (!is_dir(__DIR__ . '/../../../../app/logs')) {
                mkdir(__DIR__ . '/../../../../app/logs');
            }

            file_put_contents(__DIR__ . '/../../../../app/logs/logfile_' . date('o-m-j') . '.txt', $entireStr, FILE_APPEND);

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