<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 1.0
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace Asatru\Dotenv {
    /**
     * This components handles the .env management
     */
    class DotEnvParser {
        private $vars = [];
        private $error = '';

        /**
         * Handle as singleton class
         * 
         * @return Asatru\Dotenv\DotEnvParser
         */
        public static function instance()
        {
            static $inst = null;
            if ($inst == null) {
                $inst = new DotEnvParser();
            }

            return $inst;
        }

        /**
         * Split items of line
         * 
         * @param string $line The current line
         * @return array A key-value pair of the current variable
         */
        private function splitItems($line)
        {
            $varname = '';
            $varvalue = '';
            $err = true;
            
            for ($i = 0; $i < strlen($line); $i++) {
                if ($line[$i] == '=') {
                    $varvalue = substr($line, $i + 1);
                    $err = false;

                    break;
                }
                
                $varname .= $line[$i];
            }

            if ($err) {
                $this->error = 'Syntax error: Required \"=\" missing in line \"' . $line . '\"';
                return [];
            }

            return ['varname' => $varname, 'varvalue' => $varvalue];
        }

        /**
         * Filter variable
         * 
         * @param string The value of a variable
         * @return string The new value of the variable with the replaced variable value
         */
        private function filterVar($value)
        {
            $result = $value;

            foreach ($this->vars as $var) {
                if (strstr($value, '${' . $var['varname'] . '}') !== false) {
                    $result = str_replace('${' . $var['varname'] . '}', $var['varvalue'], $result); 
                }				
            }
            
            return $result;
        }

        /**
         * Filter string
         * 
         * @param string $input The input string to be filtered
         * @return string
         */
        private function filterString($input)
        {
            $result = '';
            $err = true;

            for ($i = strpos($input, '"') + 1; $i < strlen($input); $i++) {
                if ($input[$i] == '"') {
                    $err = false;
                    break;
                }

                $result .= $input[$i];
            }

            if ($err) {
                $this->error = 'Syntax error: missing ending quotation in input \"' . $input . '\"';
                return '';
            }

            return $result;
        }

        /**
         * Examine data type and store value accordingly
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
         * Parse .env file
         * 
         * @param string $input The absolute path to the .env file
         * @return void
         */
        public function parse($input)
        {
            $this->error = '';

            $content = file_get_contents($input);
            if (strlen($content) > 0) {
                $lines = preg_split("/\r\n|\n|\r/", $content);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (strlen($line) == 0)
                        continue;

                    if ($line[0] == '#')
                        continue;

                    $items = $this->splitItems($line);

                    if (count($items) == 2) {
                        $items['varvalue'] = trim($items['varvalue']);

                        if (strlen($items['varvalue']) > 0) {
                            $items['varvalue'] = $this->getAsDataType($items['varvalue']);
                            $items['varvalue'] = $this->filterVar($items['varvalue']);
                            array_push($this->vars, $items);
                            $_ENV[$items['varname']] = $items['varvalue'];
                        }
                    } else {
                        $this->error = 'Syntax error: missing \"=\" in line \"' . $line . '\"';
                    }
                }
            } else if ($content == false) {
                $this->error = 'I/O error: File \"' . $input . '\" could not be accessed';
            }
        }

        /**
         * Clear vars
         * 
         * @return void
         */
        public function clear()
        {
            $this->vars = [];
        }

        /**
         * Query item
         * 
         * @param string $item The variable name to be queried
		 * @param mixed $fallback optional A default value to be returned
         * @return mixed Depends of the data type of the variable
         */
        public function query($item, $fallback = null)
        {
            foreach ($this->vars as $var) {
                if ($var['varname'] == $item) {
                    return $var['varvalue'];
                }
            }
            
            return $fallback;
        }

        /**
         * Return whether there is an error
         * 
         * @return boolean
         */
        public function has_error()
        {
            return strlen($this->error) > 0;
        }

        /**
         * Return error string
         * 
         * @return string A description of the last occured error
         */
        public function errorStr()
        {
            return $this->error;
        }
    }
}

namespace {
    /**
     * Parse .env file
     * 
     * @param string $input The absolute path to the .env file
     * @return void
     */
    function env_parse($input = '.env')
    {
        Asatru\Dotenv\DotEnvParser::instance()->parse($input);
    }

    /**
     * Query item
     * 
     * @param string $item The variable name to be queried
	 * @param mixed $fallback optional A default value to be returned
     * @return mixed Depends of the data type of the variable
     */
    function env_get($item, $fallback = null)
    {
        return Asatru\Dotenv\DotEnvParser::instance()->query($item, $fallback);
    }

    /**
     * Clear vars
     * 
     * @return void
     */
    function env_clear()
    {
        return Asatru\Dotenv\DotEnvParser::instance()->clear();
    }

    /**
     * Return whether there is an error
     * 
     * @return boolean
     */
    function env_has_error()
    {
        return Asatru\Dotenv\DotEnvParser::instance()->has_error();
    }

    /**
     * Return error string
     * 
     * @return string A description of the last occured error
     */
    function env_errorStr()
    {
        return Asatru\Dotenv\DotEnvParser::instance()->errorStr();
    }
}
