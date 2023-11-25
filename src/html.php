<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2023 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace {
    /**
     * This component handles html creation
     */
    class Html {
        /**
         * Render a tag
         * 
         * @param $name The tag name of the element
         * @param array $attr optional Array of attributes to be rendered
         * @return string
         */
        public static function renderTag($name, array $attr = array())
        {
            $result = '<' . $name;

            foreach ($attr as $key => $value) {
                if (($value !== null)) {
                    $result .= ' ' . $key . '="' . $value . '"';
                } else {
                    $result .= ' ' . $key;
                }
            }

            $result .= '>';

            return $result;
        }

        /**
         * Render a closing tag
         * 
         * @param $name The tag name of the element
         * @return string
         */
        public static function renderCloseTag($name)
        {
            return '</' . $name . '>';
        }
    }
}