<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

namespace Asatru\Dotenv {
    //This components handles the .env management
    class DotEnvParser {
        private $vars = [];
        private $error = '';

        public static function instance()
        {
            //Handle as singleton class

            static $inst = null;
            if ($inst == null) {
                $inst = new DotEnvParser();
            }

            return $inst;
        }

        private function splitItems($line)
        {
            //Split items of line

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

        private function filterVar($value)
        {
            //Filter variable

            $result = $value;

            foreach ($this->vars as $var) {
                if (strstr($value, '${' . $var['varname'] . '}') !== false) {
                    $result = str_replace('${' . $var['varname'] . '}', $var['varvalue'], $result); 
                }				
            }
            
            return $result;
        }

        private function filterString($input)
        {
            //Filter string

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

        private function getAsDataType($input)
        {
            //Examine data type and store value accordingly

            $input = trim($input);

            if (strpos($input, '"') !== false) {
                return $this->filterString($input);
            } else if (strtolower($input) == 'null') {
                return null;
            } else if (is_numeric($input)) {
                return strpos($input, '.') ? floatval($input) : intval($input);
            } else if ((strtolower($input) == 'false') or (strtolower($input) == 'true')) {
                return (strtolower($input) == 'false') ? false : true;
            } else {
                return $input;
            }
        }

        public function parse($input)
        {
            //Parse .env file

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

        public function clear()
        {
            //Clear vars

            $this->vars = [];
        }

        public function query($item)
        {
            //Query item

            foreach ($this->vars as $var) {
                if ($var['varname'] == $item) {
                    return $var['varvalue'];
                }
            }
            
            return '';
        }

        public function has_error()
        {
            //Return whether there is an error

            return strlen($this->error) > 0;
        }

        public function errorStr()
        {
            //Return error string

            return $this->error;
        }
    }
}

namespace {
    function env_parse($input = '.env')
    {
        //env parser convenience function

        return Asatru\Dotenv\DotEnvParser::instance()->parse($input);
    }

    function env_get($item)
    {
        //env acessor convenience function

        return Asatru\Dotenv\DotEnvParser::instance()->query($item);
    }

    function env_clear()
    {
        //env clear convenience function

        return Asatru\Dotenv\DotEnvParser::instance()->clear();
    }

    function env_has_error()
    {
        //env has_error convenience function

        return Asatru\Dotenv\DotEnvParser::instance()->has_error();
    }

    function env_errorStr()
    {
        //env errorStr convenience function

        return Asatru\Dotenv\DotEnvParser::instance()->errorStr();
    }
}
