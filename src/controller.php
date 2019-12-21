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

//This class defines the layout of a validator class
abstract class BaseValidator {
	//Shall return the name of this validator
	abstract public function getIdent();

	//Shall validate a token
	abstract public function verify($value, $args = null);

	//Shall return an error description if any
	abstract public function getError();

	//This is just a helper to split provided arguments
	public function args($args)
	{
		return explode(',', $args);
	}
}

//This component manages custom post validators
class CustomPostValidators {
	static $validators;

	public static function load($dir)
	{
		//Load all validators

		static::$validators = array();

		if (!is_dir($dir)) {
			return;
		}

		$list = scandir($dir); //Get all items of the directory
		foreach ($list as $item) { //Iterate through that directory
			if ((!is_dir($dir . '/' . $item)) && (pathinfo($dir . '/' . $item, PATHINFO_EXTENSION) === 'php')) { //If it is a PHP script file
				//Require the script file
				require_once $dir . '/' . $item;

				//Instance validator class and store validator ident
				$className = ucfirst(pathinfo($item, PATHINFO_FILENAME)) . 'Validator';
				$validator['instance'] = new $className;
				$validator['ident'] = $validator['instance']->getIdent();

				//Add to list
				array_push(static::$validators, $validator);
			}
		}
	}

	public static function findValidator($ident)
	{
		//Find validator by its ident

		foreach (static::$validators as $validator) {
			if ($validator['ident'] === $ident) {
				return $validator;
			}
		}

		return null;
	}
}

//This components handles POST data validation
class PostValidator {
	private $attributes = [];
	private $errmsg = '';
	private $isValid = false;

	public function __construct($attribs)
	{
		//Pass attribute array

		$this->attributes = $attribs;

		$this->isValid = $this->validate();
	}

	private function validate()
	{
		//Validate whether the POST data is valid. Also check csrf token

		//Check csrf token first
		if ((!isset($_POST['csrf_token'])) || ($_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
			$this->errmsg = __('errors.csrf_token_invalid');
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
						$this->errmsg = __('errors.item_required', ['key' => $key]);
						return false;
					}
				} else if ($token == 'email') { //Check for valid E-mail address
					if ((!isset($_POST[$key]) || (filter_var($_POST[$key], FILTER_VALIDATE_EMAIL) === false))) {
						$this->errmsg = __('errors.item_email', ['key' => $key]);
						return false;
					}
				} else if (strpos($token, 'min:') === 0) { //Check for minimum string length
					if ((!isset($_POST[$key])) || (strlen($_POST[$key]) < strval(substr($token, 4)))) {
						$this->errmsg = __('errors.item_too_short', ['key' => $key, 'min' => substr($token, 4)]);
						return false;
					}
				} else if (strpos($token, 'max:') === 0) { //Check for maximum string length
					if ((!isset($_POST[$key])) || (strlen($_POST[$key]) > strval(substr($token, 4)))) {
						$this->errmsg = __('errors.item_too_large', ['key' => $key, 'max' => substr($token, 4)]);
						return false;
					}
				} else if (strpos($token, 'datetime:') === 0) { //Check for valid date/time
					if (isset($_POST[$key])) {
						$format = substr($token, 5);
						$dt = \DateTime::createFromFormat($format, $_POST[$key]);
						if ((!$dt) || ($dt->format($format) !== $_POST[$key])) {
							$this->errmsg = __('errors.item_datetime', ['key' => $key]);
							return false;
						}
					}
				} else if ($token == 'number') { //Check for valid number
					if ((!isset($_POST[$key])) || (!is_numeric($_POST[$key]))) {
						$this->errmsg = __('errors.item_number', ['key' => $key]);
						return false;
					}
				} else { //Handle custom validation if found
					$valIdent = (strpos($token, ':') !== false) ? substr($token, 0, strpos($token, ':')) : $token;
					$validator = CustomPostValidators::findValidator($valIdent);
					if ($validator !== null) {
						$args = (strpos($token, ':') !== false) ? substr($token, strpos($token, ':') + 1) : null;
						if (!$validator['instance']->verify($_POST[$key], $args)) {
							$this->errmsg = $validator['instance']->getError();
							return false;
						}
					}
				}
			}
		}

		return true;
	}

	public function isValid()
	{
		//Indicate validation result

		return $this->isValid;
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

	private function urlMatches($url, $method, $route, $ctrlArgs)
	{
		//Determine whether this route matches the given URL for the given request method
		
		if (strtolower($method) !== 'any') {
			if (strtolower($method) !== strtolower($_SERVER['REQUEST_METHOD'])) {
				return false;
			}
		}

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

	private function get404Handler()
	{
		//Get 404 handler if exists

		for ($i = 0; $i < count($this->routes); $i++) {
			if ($this->routes[$i][0] === '$404') {
				return $this->routes[$i];
			}
		}

		return null;
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
		for ($i = 0; $i < count($this->routes); $i++) {
			if (($this->routes[$i][0][0] != '$') && ($this->urlMatches($url, $this->routes[$i][1], $this->routes[$i][0], $ctrl))) {
				//Query controller file and handler function name and acquire and call. At last return the view object

				$items = explode('@', $this->routes[$i][2]);
				if (count($items) != 2) {
					throw new \Exception('Erroneous handler specified: ' . $this->routes[$i][2]);
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
		$err404Handler = $this->get404Handler();
		$result = 'The requested resource is not found on the server.';
		if ($err404Handler !== null) {
			$items = explode('@', $err404Handler[2]);
			if (count($items) != 2) {
				throw new \Exception('Erroneous handler specified: ' . $err404Handler[2]);
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
		}
		return $result;
	}
}