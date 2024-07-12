<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2024 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace Asatru\Controller;

/**
 * This component handles the internal PHP URL arguments
 */
class PHPArgs {
	private $args = [];
	
	/**
	 * Fill the args array with POST and GET values
	 */
	public function __construct()
	{
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

		//Acquire PHP input data
		$inputdata = json_decode(file_get_contents('php://input'), true);
		if ($inputdata !== null) {
			foreach ($inputdata as $key => $value) {
				$this->args[$key] = $value;
			}
		}
	}
	
	/**
	 * Query a variable
	 * 
	 * @param string $name The name of the variable
	 * @param mixed $fallback The fallback value
	 * @return mixed The value of the variable
	 */
	public function query($name, $fallback = null)
	{
		return (isset($this->args[$name])) ? $this->args[$name] : $fallback;
	}
	
	/**
	 * Query entire variable object
	 * 
	 * @return array The array containing the variables
	 */
	public function all()
	{
		return $this->args;
	}
}

/**
 * This component handles the URL included arguments
 */
class ControllerArg {
	private $args = [];
	private $phpargs = null;
	
	/**
	 * Store splitted URL arguments and save PHP arg variables
	 */
	public function __construct($url)
	{
		//Split URL into arguments and also aquire the internal arguments
		$this->args = explode('/', $url);
		$this->phpargs = new PHPArgs();
	}
	
	/**
	 * Return the object to the internal arg manager
	 * 
	 * @return Asatru\Controller\PHPArgs Instance of the PHP args manager 
	 */
	public function params()
	{
		return $this->phpargs;
	}

	/**
	 * Add argument value
	 * 
	 * @param string $name The name of the variable
	 * @param mixed $value The variable value
	 * @return void
	 */
	public function addArg($name, $value)
	{
		$this->args[$name] = $value;
	}
	
	/**
	 * Query URL specific argument
	 * 
	 * @param mixed $id The identifier of the argument
	 * @param mixed $fallback The fallback data
	 * @return string The value of the argument variable
	 */
	public function arg($id, $fallback = null)
	{
		return isset($this->args[$id]) ? $this->args[$id] : $fallback;
	}
	
	/**
	 * Get amount of URL specific arguments
	 * 
	 * @return int
	 */
	public function count()
	{
		return count($this->args);
	}
}

/**
 * This class defines the layout of a validator class
 */
abstract class BaseValidator {
	/**
	 * Shall return the name of this validator
	 * 
	 * @return string The identifier of the validator
	 */
	abstract public function getIdent();

	/**
	 * Shall validate a token
	 * 
	 * @param mixed $value The value of the item to be verified
	 * @param string $args optional The validator arguments if any
	 * @return boolean True if the item is valid, otherwise false
	 */
	abstract public function verify($value, $args = null);

	/**
	 * Shall return an error description if any
	 * 
	 * @return string A description of the error
	 */
	abstract public function getError();

	/**
	 * This is just a helper to split provided arguments
	 * 
	 * @param string $args The arguments of the validator each separated by a comma
	 * @return array An array containing each validator argument
	 */
	public function args($args)
	{
		return explode(',', $args);
	}
}

/**
 * This component manages custom post validators
 */
class CustomPostValidators {
	static $validators;

	/**
	 * Load all validators
	 * 
	 * @param string $dir The target directory containing the validators
	 * @return void
	 */
	public static function load($dir)
	{
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

	/**
	 * Find validator by its ident
	 * 
	 * @param string $ident The identifier of the validator
	 * @return array An array containing the ident and an instance of the validator
	 */
	public static function findValidator($ident)
	{
		foreach (static::$validators as $validator) {
			if ($validator['ident'] === $ident) {
				return $validator;
			}
		}

		return null;
	}
}

/**
 * This components handles POST data validation
 */
class PostValidator {
	private $attributes = [];
	private $errmsgs = [];
	private $isValid = false;

	/**
	 * Pass attribute array and validate the data
	 * 
	 * @param array $attribs The validation attributes
	 */
	public function __construct($attribs)
	{
		$this->attributes = $attribs;
		$this->validate();
	}

	/**
	 * Validate whether the POST data is valid. Also check csrf token
	 * 
	 * @return void
	 */
	private function validate()
	{
		//Clear array
		$this->errmsgs = [];

		//Check csrf token first
		if ((!isset($_POST['csrf_token'])) || ($_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
			$this->errmsgs[] = __('errors.csrf_token_invalid');
		}

		//Next check the rest
		foreach ($this->attributes as $key => $value) {
			//Get tokens
			$tokens = explode('|', ((strlen($value) > 0) && (strpos($value, '|') === false)) ? $value : $value . '|');
			
			//Check each token
			foreach ($tokens as $token) {
				if (strlen($token) === 0) {
					continue;
				}

				if ($token == 'required') { //Specify that this POST data object must be provided
					if (!isset($_POST[$key])) {
						$this->errmsgs[] = __('errors.item_required', ['key' => $key]);
					}
				} else if ($token == 'email') { //Check for valid E-mail address
					if ((!isset($_POST[$key]) || (filter_var($_POST[$key], FILTER_VALIDATE_EMAIL) === false))) {
						$this->errmsgs[] = __('errors.item_email', ['key' => $key]);
					}
				} else if (strpos($token, 'min:') === 0) { //Check for minimum string length
					if ((!isset($_POST[$key])) || (strlen($_POST[$key]) < strval(substr($token, 4)))) {
						$this->errmsgs[] = __('errors.item_too_short', ['key' => $key, 'min' => substr($token, 4)]);
					}
				} else if (strpos($token, 'max:') === 0) { //Check for maximum string length
					if ((!isset($_POST[$key])) || (strlen($_POST[$key]) > strval(substr($token, 4)))) {
						$this->errmsgs[] = __('errors.item_too_large', ['key' => $key, 'max' => substr($token, 4)]);
					}
				} else if (strpos($token, 'datetime:') === 0) { //Check for valid date/time
					if (isset($_POST[$key])) {
						$format = substr($token, strlen('datetime:'));
						$dt = \DateTime::createFromFormat($format, $_POST[$key]);
						if ((!$dt) || ($dt->format($format) !== $_POST[$key])) {
							$this->errmsgs[] = __('errors.item_datetime', ['key' => $key]);
						}
					}
				} else if ($token == 'number') { //Check for valid number
					if ((!isset($_POST[$key])) || (!is_numeric($_POST[$key]))) {
						$this->errmsgs[] = __('errors.item_number', ['key' => $key]);
					}
				} else if (strpos($token, 'regex:') === 0) { //Check against regex pattern
					$pattern = substr($token, strlen('regex:'));
					if ((!isset($_POST[$key])) || (preg_match($pattern, $_POST[$key]) !== 1)) {
						$this->errmsgs[] = __('errors.item_regex', ['key' => $key, 'pattern' => $pattern]);
					}
				} else { //Handle custom validation if found
					$valIdent = (strpos($token, ':') !== false) ? substr($token, 0, strpos($token, ':')) : $token;
					$validator = CustomPostValidators::findValidator($valIdent);
					if ($validator !== null) {
						$args = (strpos($token, ':') !== false) ? substr($token, strpos($token, ':') + 1) : null;
						if (!$validator['instance']->verify($_POST[$key], $args)) {
							$this->errmsgs[] = $validator['instance']->getError();
						}
					}
				}
			}
		}
	}

	/**
	 * Indicate validation result
	 * 
	 * @return boolean
	 */
	public function isValid()
	{
		return count($this->errmsgs) === 0;
	}

	/**
	 * Return the array of error messages
	 * 
	 * @return array An array containing error messages of all invalid items
	 */
	public function errorMsgs()
	{
		return $this->errmsgs;
	}
}

/**
 * This components handles temporary POST data storage
 */
class OldPostData {
	/**
	 * Store POST data in session
	 * 
	 * @return void
	 */
	public static function store()
	{
		if ((isset($_POST)) && (is_array($_POST))) {
			$_SESSION['asatru_post'] = array();
			foreach ($_POST as $key => $value) {
				$_SESSION['asatru_post'][$key] = $value;
			}
		}
	}

	/**
	 * Store POST data if currently handling a POST route
	 * 
	 * @return void
	 */
	public static function handle()
	{
		if ((isset($_POST)) && (is_array($_POST))) {
			if (!isset($_SESSION['asatru_post'])) {
				static::store();
			}
		}
	}

	/**
	 * Clear session POST data
	 * 
	 * @return void
	 */
	public static function clear()
	{
		if (isset($_SESSION['asatru_post'])) {
			unset($_SESSION['asatru_post']);
		}
	}
}

/**
 * This components represents a base controller
 */
abstract class Controller {
	/**
	 * Called before the actual route handler is being called
	 * @throws \Exception For example throw an access denied action for a given user
	 * @return void
	 */
	public function preDispatch()
	{
	}

	/**
	 * Called after the actual route handler is being called
	 * @throws \Exception For example throw an access denied action for a given user
	 * @return void
	 */
	public function postDispatch()
	{
	}
}

/**
 * This component passes control to the given controller associated with the current URL
 */
class ControllerHandler {
	private $routes = [];
	
	/**
	 * Acquire the routes configuration
	 * 
	 * @param string $routesFile The absolute path to the route configuration file
	 * @return void
	 */
	public function __construct($routesFile)
	{
		$this->routes = require($routesFile);
	}

	/**
	 * Get current request method
	 * 
	 * @return string
	 */
	private function getRequestMethod()
	{
		if (isset($_POST['_method'])) {
			return $_POST['_method'];
		}

		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Determine if current request is a synchronized GET request
	 * 
	 * @return bool
	 */
	private function isSynchronizedGetRequest()
	{
		return (strtolower($this->getRequestMethod()) === 'get') && ((!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'));
	}

	/**
	 * Determine whether this route matches the given URL for the given request method
	 * 
	 * @param string $url The current URL to be handled
	 * @param string $method The request method
	 * @param string $route The route to be checked against the URL
	 * @param Asatru\Controller\ControllerArg Instance to add the arguments found in the URL according to definition in the route
	 * @return boolean
	 * @throws \Exception
	 */
	private function urlMatches($url, $method, $route, $ctrlArgs)
	{
		if (strtolower($method) !== 'any') {
			if (strtolower($method) !== strtolower($this->getRequestMethod())) {
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

	/**
	 * Get 404 handler if exists
	 * 
	 * @return array|null The 404 handler item of the routes configuration if found, otherwise null
	 */
	private function get404Handler()
	{
		for ($i = 0; $i < count($this->routes); $i++) {
			if ($this->routes[$i][0] === '$404') {
				return $this->routes[$i];
			}
		}

		return null;
	}

	/**
	 * Get server code handler if exists
	 * 
	 * @return array|null The server code handler item of the routes configuration if found, otherwise null
	 */
	private function getServerCodeHandler($code)
	{
		for ($i = 0; $i < count($this->routes); $i++) {
			if ($this->routes[$i][0] === '$' . $code) {
				return $this->routes[$i];
			}
		}

		return null;
	}

	/**
	 * Get named route and replace values if exist
	 * 
	 * @param $name Name of the route
	 * @param $values Additional key-value paired array to replace actual values
	 * @return string|null
	 */
	public function getNamedRoute($name, $values = [])
	{
		for ($i = 0; $i < count($this->routes); $i++) {
			if ((isset($this->routes[$i][3])) && ($this->routes[$i][3] === $name)) {
				$result = $this->routes[$i][0];

				foreach ($values as $key => $value) {
					$result = str_replace('{' . $key . '}', $value, $result);
				}

				return $result;
			}
		}

		return null;
	}
	
	/**
	 * Parse the URL and call the associated controller
	 * 
	 * @param string $url The URL to be handled
	 * @return mixed|string The result data of the called controller, the result of the 404 handler or a default string
	 * @throws \Exception
	 */
	public function parse($url)
	{
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
				
				if (file_exists(ASATRU_APP_ROOT . '/app/controller/_base.php')) {
					require_once ASATRU_APP_ROOT . '/app/controller/_base.php';
				}
				
				require_once ASATRU_APP_ROOT . '/app/controller/' . $items[0] . '.php';
				require_once "view.php";

				if (strtolower($this->getRequestMethod()) !== 'get') {
					OldPostData::handle();
				}
				
				$className = ucfirst($items[0]) . 'Controller';
				$obj = new $className();

				if (method_exists($obj, $items[1])) {
					if (method_exists($obj, 'preDispatch')) {
						call_user_func(array($obj, 'preDispatch'));
					}

					$result = call_user_func(array($obj, $items[1]), $ctrl);

					if (method_exists($obj, 'postDispatch')) {
						call_user_func(array($obj, 'postDispatch'));
					}

					if ($this->isSynchronizedGetRequest()) {
						$_SESSION['asatru_last_url'] = $_SERVER['REQUEST_URI'];
					}
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
			if (file_exists(ASATRU_APP_ROOT . '/app/controller/_base.php')) {
				require_once ASATRU_APP_ROOT . '/app/controller/_base.php';
			}
			require_once ASATRU_APP_ROOT . '/app/controller/' . $items[0] . '.php';
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

	/**
	 * Abort with server response code
	 * 
	 * @param $code The response code to send
	 * @param $ctrl An instance of ControllerArg
	 * @return mixed|string The result of the controller handler or a default string
	 * @throws \Exception
	 */
	public function abort($code, $ctrl = null)
	{
		header("HTTP/1.0 {$code}");
		$codeHandler = $this->getServerCodeHandler($code);
		$result = 'Server response code: ' . $code;
		if ($codeHandler !== null) {
			$items = explode('@', $codeHandler[2]);
			if (count($items) != 2) {
				throw new \Exception('Erroneous handler specified: ' . $codeHandler[2]);
			}
			if (file_exists(ASATRU_APP_ROOT . '/app/controller/_base.php')) {
				require_once ASATRU_APP_ROOT . '/app/controller/_base.php';
			}
			require_once ASATRU_APP_ROOT . '/app/controller/' . $items[0] . '.php';
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