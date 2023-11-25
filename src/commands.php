<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2023 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace Asatru\Commands {
    /**
     * This component handles a single argument
     */
    class Argument {
        private $value;

        /**
         * Create object
         * 
         * @param $value
         * @return void
         */
        public function __construct($value)
        {
            $this->value = $this->getAsDataType($value);
        }

        /**
         * Examine data type and return value accordingly
         * 
         * @param string $input The input string (variable value)
         * @return mixed Depends on the data type of the input
         */
        private function getAsDataType($input)
        {
            $input = trim($input);

            if (strpos($input, '"') !== false) {
                return $this->filterString($input);
            } else if (strtolower($input) == 'null') {
                return null;
            } else if (is_numeric($input)) {
                return strpos($input, '.') ? floatval($input) : intval($input);
            } else if ((strtolower($input) == 'false') || (strtolower($input) == 'off') || (strtolower($input) == 'no')) {
                return false;
            } else if ((strtolower($input) == 'true') || (strtolower($input) == 'on') || (strtolower($input) == 'yes')) {
                return true;
            } else {
                return $input;
            }
        }

        /**
         * Get value of argument
         * 
         * @return string
         */
        public function getValue()
        {
            return $this->value;
        }

        /**
         * Get value type
         * 
         * @return string
         */
        public function getType()
        {
            return gettype($this->value);
        }

        /**
         * Indicate if value is null
         * 
         * @return bool
         */
        public function isNull()
        {
            return is_null($this->value);
        }

        /**
         * Indicate if value is empty
         * 
         * @return bool
         */
        public function isEmpty()
        {
            return empty($this->value);
        }
    }

    /**
     * This component handles the command arguments
     */
    class Arguments implements \Iterator, \Countable {
        private $items = array();
        private $position = 0;

        /**
         * Create object
         * 
         * @param $args
         * @return void
         */
        public function __construct($args)
        {
            $this->position = 0;
            
            foreach ($args as $arg) {
                $this->items[] = new Argument($arg);
            }
        }

        /**
         * Return amount of items
         * 
         * @return int
         */
        public function count(): int
        {
            return count($this->items);
        }

        /**
         * Query item entry value
         * 
         * @param mixed $ident The ident of the object
         * @return Asatru\Database\Collection|mixed The value of the item, can be a Collection, too
         */
        public function get($ident)
        {
            if (isset($this->items[$ident])) {
                return $this->items[$ident];
            }

            return null;
        }

        /**
         * Get first element
         * 
         * @return mixed
         */
        public function first()
        {
            return (isset($this->items[0])) ? $this->items[0] : null;
        }

        /**
         * Get last element
         * 
         * @return mixed
         */
        public function last()
        {
            return (isset($this->items[count($this->items)-1])) ? $this->items[count($this->items)-1] : null;
        }

        /**
         * Iterate through entries and inform a callback function
         * 
         * @param closure $callback The function to be called for each item
         * @param array $data optional A key-value paired array containing data to pass to the callback function
         * @return void
         */
        public function each($callback, array $data = null)
        {
            foreach ($this->items as $ident => $item) {
                if ($data !== null) {
                    call_user_func_array($callback, array($ident, $item, $data));
                } else {
                    call_user_func_array($callback, array($ident, $item));
                }
            }
        }

        /**
         * Indicate validity of item index
         * 
         * @return bool
         */
        public function valid(): bool
        {
            return isset($this->items[$this->position]);
        }

        /**
         * Get current iterated element
         * 
         * @return mixed
         */
        public function current(): mixed
        {
            return $this->items[$this->position];
        }

        /**
         * Get key index value
         * 
         * @return int
         */
        public function key(): mixed
        {
            return $this->position;
        }
        
        /**
         * Go to next entry
         * 
         * @return void
         */
        public function next(): void
        {
            ++$this->position;
        }
        
        /**
         * Reset index pointer
         * 
         * @return void
         */
        public function rewind(): void
        {
            $this->position = 0;
        }
    }

    /**
     * Command interface
     */
    interface Command {
        function handle($args);
    }

    /**
     * This component handles the custom commands
     */
    class CustomCommands {
        private $cmds = [];
        private $args = [];

        /**
         * Create object
         * 
         * @param $config
         * @return void
         */
        public function __construct($config, $args)
        {
            $this->args = $args;
            $this->loadCommandConfig($config);
        }

        /**
         * Load command configuration file
         * 
         * @param string $config The absolute path to the command configuration file
         * @return void
         * @throws \Exception
         */
        private function loadCommandConfig($config)
        {
            $arr = require($config);
            if (is_array($arr) === false) {
                throw new \Exception('Invalid commands configuration file');
            }

            $this->cmds = [];

            foreach ($arr as $key => $item) {
                if ((!is_array($item)) || (count($item) !== 3)) {
                    throw new \Exception('Command item ' . $key . ' is invalid');
                }

                $this->cmds[$item[0]]['description'] = $item[1];
                $this->cmds[$item[0]]['class'] = $item[2];
            }
        }

        /**
         * Handle custom command
         * 
         * @param $name
         * @return void
         * @throws \Exception
         */
        public function handleCommand($name)
        {
            if (!isset($this->cmds[$name])) {
                throw new \Exception('Command ' . $name . ' not found');
            }

            $objArgs = new Arguments(array_slice($this->args, 2));

            require_once ASATRU_APP_ROOT . '/app/commands/' . $this->cmds[$name]['class'] . '.php';

            $cls = new $this->cmds[$name]['class'];
            $cls->handle($objArgs);
        }

        /**
         * Get list of all commands
         * 
         * @return array
         * @throws \Exception
         */
        public function getAll()
        {
            return $this->cmds;
        }
    }
}

namespace {
    $objCustomCommands = new Asatru\Commands\CustomCommands(ASATRU_APP_ROOT . '/app/config/commands.php', $argv);

    /**
     * Handle custom command via associated manager
     * 
     * @param $name
     * @return void
     * @throws \Exception
     */
    function handle_custom_command($name)
    {
        global $objCustomCommands;

        $objCustomCommands->handleCommand($name);
    }

    /**
     * Get custom command list via associated manager
     * 
     * @return array
     * @throws \Exception
     */
    function get_custom_commands()
    {
        global $objCustomCommands;

        return $objCustomCommands->getAll();
    }
}