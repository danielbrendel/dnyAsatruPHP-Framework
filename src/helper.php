<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

namespace {
    /**
     * Actual flash message item
     */
    class FlashMsgItem {
        private $name = null;
        private $msg = null;

        /**
         * Set identifier and message
         * 
         * @param string $name The identifier of the message
         * @param string $msg The actual message text
         * @return void
         */
        public function __construct($name, $msg)
        {
            $this->name = $name;
            $this->msg = $msg;
        }

        /**
         * Get name
         * 
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * Get message
         * 
         * @return string
         */
        public function getMsg()
        {
            return $this->msg;
        }
    }

    /**
     * This components handles flash messages
     */
    class FlashMessage {
        /**
         * Set new flash message
         * 
         * @param string $name The identifier of the message
         * @param string $msg The actual message text
         * @return void
         */
        public static function setMsg($name, $msg)
        {
            $_SESSION[$name] = new FlashMsgItem($name, $msg);
        }

        /**
         * Check if flash message exists
         * 
         * @param string $name The identifier of the flash message
         * @return boolean
         */
        public static function hasMsg($name)
        {
            return (isset($_SESSION[$name]) && ($_SESSION[$name]) instanceof FlashMsgItem);
        }

        /**
         * Get flash message
         * 
         * @param string $name The identifier of the flash message
         * @return string|boolean The message text or false if not found
         */
        public static function getMsg($name)
        {
            if ((!isset($_SESSION[$name])) || (!($_SESSION[$name] instanceof FlashMsgItem))) {
                return false;
            }

            return $_SESSION[$name]->getMsg();
        }

        /**
         * Clear all flash messages
         * 
         * @return void
         */
        public static function clearAll()
        {
            foreach ($_SESSION as $key => $item) {
                if ($item instanceof FlashMsgItem) {
                    unset($_SESSION[$key]);
                }
            }
        }
    }

    /**
     * Return root path of project
     * 
     * @return string
     */
    function base_path()
    {
        return str_replace('\\', '/', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
    }

    /**
     * Return path to app directory
     * 
     * @return string
     */
    function app_path()
    {
        return base_path() . '/app';
    }

    /**
     * Return path to resource directory
     * 
     * @return string
     */
    function resource_path()
    {
        return base_path() . '/app/resources';
    }

    /**
     * Return the base URL
     * 
     * @return string
     */
    function base_url()
    {
        return (((isset($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] != 'off')) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . (($_SERVER['SERVER_PORT'] != 80) ? ':' . $_SERVER['SERVER_PORT'] : '') . $_ENV['APP_BASEDIR'];
    }

    /**
     * Return URL to app directory
     * 
     * @return string
     */
    function app_url()
    {
        return base_url() . '/app';
    }

    /**
     * Return URL to resources directory
     * 
     * @return string
     */
    function resource_url()
    {
        return app_url() . '/resources';
    }

    /**
     * Return the CSRF token
     * 
     * @return string
     */
    function csrf_token()
    {
        return $_SESSION['csrf_token'];
    }
}