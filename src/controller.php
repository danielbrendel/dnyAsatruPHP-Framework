<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

namespace Asatru\Controller;

//This component handles the internal PHP URL arguments
class PHPArgs {
	private $args = [];
	
	public function __construct()
	{
		//Instantiate object

		//Aquire POST variables
		if (isset($_POST)) {
			foreach ($_POST as $key => $value) {
				$this->args[$key] = $value;
			}
		}
		
		//Aquire GET variables
		if (isset($_GET)) {
			foreach ($_GET as $key => $value) {
				$this->args[$key] = $value;
			}
		}
	}
	
	public function query($name)
	{
		//Query a variable

		return (isset($this->args[$name])) ? $this->args[$name]: '';
	}
	
	public function all()
	{
		//Query entire variable object

		return $this->args;
	}
}

//This component handles the URL included arguments
class ControllerArg {
	private $args = [];
	private $phpargs = null;
	
	public function __construct($url)
	{
		//Instantiate object

		//Split URL into arguments and also aquire the internal arguments
		$this->args = explode('/', $url);
		$this->phpargs = new PHPArgs();
	}
	
	public function request()
	{
		//Return the object to the internal arg manager

		return $this->phpargs;
	}

	public function addArg($name, $value)
	{
		//Add argument value

		$this->args[$name] = $value;
	}
	
	public function arg($id)
	{
		//Query URL specific argument

		return isset($this->args[$id]) ? $this->args[$id] : '';
	}
	
	public function count()
	{
		//Get amount of URL specific arguments

		return count($this->args);
	}
}

//This components handles POST data validation
class PostValidator {
	private $attributes = [];
	private $errmsg = [];

	public function __construct($attribs)
	{
		//Pass attribute array

		$this->attributes = $attribs;
	}

	public function isValid()
	{
		//Indicate whether the POST data is valid. Also check csrf token

		//Check csrf token first
		if ((!isset($_POST['csrf_token'])) || ($_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
			$this->errmsg = 'CSRF token is missing or invalid';
			return false;
		}

		//Next check the rest
		foreach ($this->attributes as $key => $value) {
			//Get tokens
			$tokens = explode('|', ((strlen($value) > 0) && (strpos($value, '|') === false)) ? $value : $value . '|');
			
			//Check each token
			foreach ($tokens as $token) {
				if ($token == 'required') { //Specify that this POST data object must be provided
					if (!isset($_POST[$key])) {
						$this->errmsg = 'Item ' . $key . ' is required.';
						return false;
					}
				} else if ($token == 'email') {
					if ((!isset($_POST[$key]) || (filter_var($_POST[$key], FILTER_VALIDATE_EMAIL) === false))) {
						$this->errmsg = 'Item ' . $key . ' must be a valid E-Mail address';
						return false;
					}
				} else if (strpos($token, 'min:') === 0) {
					if ((!isset($_POST[$key])) || (strlen($_POST[$key]) < strval(substr($token, 4)))) {
						$this->errmsg = 'Item length of ' . $key . ' must be greater than ' . substr($token, 4);
						return false;
					}
				} else if (strpos($token, 'max:') === 0) {
					if ((!isset($_POST[$key])) || (strlen($_POST[$key]) > strval(substr($token, 4)))) {
						$this->errmsg = 'Item length of ' . $key . ' must be less than ' . substr($token, 4);
						return false;
					}
				}
			}
		}

		return true;
	}

	public function errorMsg()
	{
		//Return the message of the last error

		return $this->errmsg;
	}
}

//This component passes control to the given controller associated with the current URL
class ControllerHandler {
	private $routes = [];
	
	public function __construct($routesFile)
	{
		//Instantiate object

		//Acquire the routes configuration
		$this->routes = require_once($routesFile);
	}

	private function urlMatches($url, $route, $ctrlArgs)
	{
		//Determine whether this route matches the given URL
		
		if (strpos($route, '{') !== false) {
			$spl1 = explode('/', substr($route, 1));
			$spl2 = explode('/', substr($url, 1));
			
			if (count($spl1) == count($spl2)) {
				for ($i = 0; $i < count($spl1); $i++) {
					if ($spl1[$i][0] == '{') {
						if ($spl1[$i][strlen($spl1[$i])-1] != '}') {
							throw new \Exception('Route ' . $route . ' has invalid syntax');
						}
						
						$ctrlArgs->addArg(substr($spl1[$i], 1, strlen($spl1[$i])-2), $spl2[$i]);
					} else {
						if ($spl1[$i] != $spl2[$i]) {
							return false;
						}
					}
				}

				return true;
			} else {
				return false;
			}
		}

		return $route === $url;
	}
	
	public function parse($url)
	{
		//Parse the URL and call the associated controller

		//Remove unneccessary URL parts so we have the relative path of the current directory with URL
		$url = str_replace(dirname(dirname(str_replace('\\', '/', substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']))))), '', $url);
		if (strpos($url, '?') !== false) {
			$url = str_replace(substr($url, strpos($url, '?')), '', $url);
		}
		
		//Instantiate arguement handler
		$ctrl = new ControllerArg($url);
		
		//Check each registered route if it matches with the desired route
		foreach ($this->routes as $key => $value) {
			if (($key[0] != '$') && ($this->urlMatches($url, $key, $ctrl))) {
				//Query controller file and handler function name and acquire and call. At last return the view object

				$items = explode('@', $value);
				if (count($items) != 2) {
					throw new \Exception('Erroneous handler specified: ' . $value);
				}
				
				require_once __DIR__ . '/../../../../app/controller/' . $items[0] . '.php';
				require_once "view.php";
				
				$className = ucfirst($items[0]) . 'Controller';
				$obj = new $className();

				if (method_exists($obj, $items[1])) {
					$result = call_user_func(array($obj, $items[1]), $ctrl);
				} else {
					throw new \Exception('Controller handler ' . $items[1]. ' not found');
				}

				return $result;
			}
		}
		
		//No handler found
		header("HTTP/1.0 404 Not Found");
		$items = explode('@', $this->routes['$404']);
		if (count($items) != 2) {
			throw new \Exception('Erroneous handler specified: ' . $value);
		}
		require_once __DIR__ . '/../../../../app/controller/' . $items[0] . '.php';
		require_once "view.php";
		$className = ucfirst($items[0]) . 'Controller';
		$obj = new $className();
		if (method_exists($obj, $items[1])) {
			$result = call_user_func(array($obj, $items[1]), $ctrl);
		} else {
			throw new \Exception('Controller handler ' . $items[1]. ' not found');
		}
		return $result;
	}
}