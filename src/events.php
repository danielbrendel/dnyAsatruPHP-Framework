<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

namespace Asatru\Events {
    /**
     * This class handles the events
     */
    class EventManager {
        private $eventlist = [];

        /**
         * Construct object loading the events config
         * 
         * @param string $config The absolute path to the events configuration file
         * @return void
         */
        public function __construct($config)
        {
            $this->loadEventConfig($config);
        }

        /**
         * Load event configuration file
         * 
         * @param string $config The absolute path to the events configuration file
         * @return void
         * @throws \Exception
         */
        private function loadEventConfig($config)
        {
            $arr = require_once($config);
            if (is_array($arr) === false) {
                throw new \Exception('Invalid events configuration file');
            }

            foreach ($arr as $key => $value) {
                if (strpos($value, '@') === false) {
                    throw new \Exception('Invalid syntax for event ' . $key);
                }

                $handler = explode('@', $value);
                $this->eventlist[$key]['class'] = $handler[0];
                $this->eventlist[$key]['method'] = $handler[1];
            }
        }

        /**
         * Call the associated handler for this event
         * 
         * @param string $name The name of the event
         * @param mixed optional Data to be passed to the event handler
         * @return void
         */
        public function raiseEvent($name, $params = null)
        {
            if (isset($this->eventlist[$name])) {
                require_once __DIR__ . '/../../../../app/events/' . strtolower($this->eventlist[$name]['class']) . '.php';

                $cls = new $this->eventlist[$name]['class'];
                call_user_func_array(array($cls, $this->eventlist[$name]['method']), array($params));
            }
        }
    }
}

namespace {
    $objEventManager = new \Asatru\Events\EventManager(__DIR__ . '/../../../../app/config/events.php');

    /**
     * Raise an event and call the associated handler
     * @param string $name The name of the event
     * @param mixed optional Data to be passed to the event handler
     * @return void
     */
    function event($name, $params = null)
    {
        global $objEventManager;

        $objEventManager->raiseEvent($name, $params);
    }
}