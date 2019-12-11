<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

namespace Asatru\Logger {
    //Logging types
    const LOG_HEADER = 0;
    const LOG_INFO = 1;
    const LOG_DEBUG = 2;
    const LOG_WARNING = 3;
    const LOG_ERROR = 4;

    //This components handles the logging
    class Logger {
        private $log = array();

        public function __construct()
        {
            //Instantiate object

            //Add initial item
            $item = ['type' => LOG_HEADER, 'text' => 'Asatru PHP log on ' . date('o-m-j h:i:s a')];
            array_push($this->log, $item);
        }

        private function getStrType($logtype)
        {
            //Get log type

            switch ($logtype) {
                case LOG_HEADER:
                    return 'Header';
                case LOG_INFO:
                    return 'Info';
                case LOG_INFO:
                    return 'Info';
                case LOG_INFO:
                    return 'Info';
                case LOG_INFO:
                    return 'Info';
                default:
                    return 'Unknown';
            }
        }

        public function add($type, $line)
        {
            //Add new log line

            $item = ['type' => $type, 'text' => $line];
            array_push($this->log, $item);
        }

        public function clear()
        {
            //Clear log content and add initial next item

            $this->log = array();

            $item = ['type' => LOG_HEADER, 'text' => 'Asatru PHP log on ' . date('o-m-j h:i:s a')];
            array_push($this->log, $item);
        }

        public function store()
        {
            //Store log content to file

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
        function addLog($type, $line)
        {
            global $objLogger;
            $objLogger->add($type, $line);
        }
    }

    if (!function_exists('clearLog')) {
        function clearLog($type, $line)
        {
            global $objLogger;
            $objLogger->clear();
        }
    }

    if (!function_exists('storeLog')) {
        function storeLog()
        {
            global $objLogger;
            $objLogger->store();
        }
    }
}