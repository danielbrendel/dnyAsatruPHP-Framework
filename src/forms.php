<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2025 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace {
    require_once 'html.php';

    /**
     * This component handles form creation
     */
    class Form extends Html {
        /**
         * Begin form 
         * 
         * @param array $attr optional Array of attributes to be rendered
         * @return string
         */
        public static function begin(array $attr = array())
        {
            return static::renderTag('form', $attr);
        }

        /**
         * Put a form element
         * 
         * @param $name The tag name of the element
         * @param array $attr optional Array of attributes to be rendered
         * @return string
         */
        public static function putElement($name, array $attr = array())
        {
            return static::renderTag($name, $attr);
        }

        /**
         * Close current element
         * 
         * @param $name The tag name of the element
         * @return string
         */
        public static function closeElement($name)
        {
            return static::renderCloseTag($name);
        }

        /**
         * End current form rendering
         * 
         * @return string
         */
        public static function end()
        {
            return static::renderCloseTag('form');
        }
    }
}