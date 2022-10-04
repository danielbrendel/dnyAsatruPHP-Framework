<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2022 by Daniel Brendel
    
    Version: 1.0
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
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
     * Carbon helper component
     */
    class Carbon extends Carbon\Carbon {
        /**
         * Construct object and set locale
         * 
         * @param $time
		 * @param $tz
         * @return void
         */
        public function __construct($time = null, $tz = null)
		{
			parent::__construct($time, $tz);
			
			$this->locale(getLocale());
		}
    }

    /**
     * Return root path of project
     * 
     * @param string $path Additional path
     * @return string
     */
    function base_path($path = '')
    {
        return str_replace('\\', '/', dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . $path;
    }

    /**
     * Return path to app directory
     * 
     * @param string $path Additional path
     * @return string
     */
    function app_path($path = '')
    {
        return base_path() . '/app' . $path;
    }

    /**
     * Return path to public directory
     * 
     * @param string $path Additional path
     * @return string
     */
    function public_path($path = '')
    {
        return base_path() . '/public' . $path;
    }

    /**
     * Return the base URL
     * 
     * @param $port
     * @return string
     */
    function base_url($port = false)
    {
        return (((isset($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] != 'off')) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . (($port !== false) ? ':' . $_SERVER['SERVER_PORT'] : '') . $_ENV['APP_BASEDIR'];
    }

    /**
     * Make a full URL to the given path
     * 
     * @param string $to An additional destination
     * @return string The full URL
     */
    function url($to = '')
    {
        $port = ($_SERVER['SERVER_PORT'] == 8000) ? true : false;

        return base_url($port) . $to;
    }

    /**
     * Return URL to asset
     * 
     * @param string $asset The path to the asset
     * @return string The full URL to the asset
     */
    function asset($asset = '')
    {
        if ((((isset($_ENV['APP_DEBUG'])) && ($_ENV['APP_DEBUG']))) && (!file_exists(public_path('/' . $asset)))) {
            throw new \Exception('Resource "' . $asset . '" not found on server.');
        }

        return url('/' . $asset);
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

    /**
     * Add template command
     * 
     * @param string $ident The command identifier
     * @param callback $callback The callback function
     * @return bool
     */
    function template_command($ident, $callback)
    {
        return Asatru\View\ViewHandler::addReplacerCommand($ident, $callback);
    }

    /**
     * View creation helper function
     * 
     * @param string $layout The layout file name
     * @param array $yields An array either containing items of yields or a single yield entry
     * @param array $vars optional Array containing variables passed to the view
     * @return Asatru\View\ViewHandler
     */
    function view($layout, array $yields, $vars = array())
    {
        $viewHandler = new Asatru\View\ViewHandler();

        $viewHandler->setLayout($layout);
        $viewHandler->setVars($vars);

        if (count($yields) === 2) {
            if ((is_string($yields[0])) && (is_string($yields[1]))) {
                $viewHandler->setYield($yields[0], $yields[1]);
                return $viewHandler;
            }
        }

        foreach ($yields as $yield) {
            $viewHandler->setYield($yield[0], $yield[1]);
        }

        return $viewHandler;
    }

    /**
     * Helper for JsonHandler
     * 
     * @param array $content
     * @return Asatru\View\JsonHandler
     */
    function json(array $content)
    {
        return new Asatru\View\JsonHandler($content);
    }

    /**
     * Helper for XmlHandler
     * 
     * @param array $content 
     * @param string $root
     * @return Asatru\View\XmlHandler
     */
    function xml(array $content, $root = 'data')
    {
        return new Asatru\View\XmlHandler($content, $root);
    }

    /**
     * Helper for CsvHandler
     * 
     * @param array $content
     * @param array $header
     * @return Asatru\View\CsvHandler
     */
    function csv(array $content, array $header = null)
    {
        return new Asatru\View\CsvHandler($content, $header);
    }
	
	/**
     * Helper for PlainHandler
     * 
     * @param string $content
     * @return Asatru\View\PlainHandler
     */
	function text($content)
	{
		return new Asatru\View\PlainHandler($content);
	}
	
	/**
     * Helper for CustomHandler
     * 
	 * @param string $type
     * @param mixed $content
     * @return Asatru\View\CustomHandler
     */
	function custom($type, $content)
	{
		return new Asatru\View\CustomHandler($type, $content);
	}
	
	/**
     * Helper for RedirectHandler
     * 
     * @param string $to
     * @return Asatru\View\RedirectHandler
     */
	function redirect($to)
	{
		return new Asatru\View\RedirectHandler($to);
	}
	
	/**
     * Helper to go back to last URL.
	 * Should be used from POST request route to get back to the related GET request route
     * 
     * @return Asatru\View\RedirectHandler
     */
	function back()
	{
		return redirect($_SERVER['REQUEST_URI']);
	}
	
	/**
     * Helper for DownloadHandler
     * 
     * @param string $resource
     * @return Asatru\View\DownloadHandler
     */
	function download($resource)
	{
		return new Asatru\View\DownloadHandler($resource);
	}

    /**
     * Helper shortname function for env_get
     * 
     * @param string $item 
     * @param mixed $fallback 
     * @return mixed
     */
    function env($item, $fallback = null)
    {
        return env_get($item, $fallback);
    }

    /**
     * Helper for controllers abort method
     * 
     * @param $code
     * @param $ctrl
     * @return mixed
     */
    function abort($code, $ctrl = null)
    {
        global $controller;

        return $controller->abort($code, $ctrl);
    }
}