<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    License: see LICENSE.txt
*/

namespace Asatru\View {
	/**
	 * Basic view interface
	 */
	interface ViewInterface {
		/**
		 * Output the actual content
		 * 
		 * @return mixed
		 */
		public function out();
	}

	/**
	 * This component handles the view rendering
	 */
	class ViewHandler implements ViewInterface {
		private $yields = [];
		private $layout = null;
		private $vars = [];
		
		/**
		 * Instantiate object
		 * 
		 * @param array $vars optional The key-value data
		 */
		public function __construct($vars = [])
		{
			$this->vars = $vars;
		}

		/**
		 * Instantiate and setup view
		 * 
		 * @param string $layout The layout file
		 * @param array $yields A key-value paired array containing the yields
		 * @param array $vars optional The key-value data
		 * @return Asatru\View\ViewHandler The view handler instance 
		 */
		public static function create($layout, array $yields, $vars = [])
		{
			$inst = new ViewHandler($vars);
			$inst->setLayout($layout);
			
			foreach ($yields as $key => $value) {
				$inst->setYield($key, $value);
			}

			return $inst;
		}
		
		/**
		 * Set the layout file
		 * 
		 * @param string $layout The name of the layout file
		 * @return Asatru\View\ViewHandler
		 */
		public function setLayout($layout)
		{
			$this->layout = $layout;
			
			return $this;
		}
		
		/**
		 * Add a yield
		 * 
		 * @param string $name The yield identifier
		 * @param string $file The yield file to be included
		 * @return Asatru\View\ViewHandler
		 */
		public function setYield($name, $file)
		{
			$this->yields[$name] = file_get_contents(__DIR__ . '/../../../../app/views/' . $file . '.php');;
			
			return $this;
		}
		
		/**
		 * Set view variables
		 * 
		 * @param array $vars An array containing the key-value pairs
		 * @return Asatru\View\ViewHandler
		 */
		public function setVars($vars)
		{
			$this->vars = $vars;
			
			return $this;
		}

		/**
		 * Whether command is there
		 * 
		 * @param string $code The code line
		 * @param string $cmd The command identifier
		 * @return boolean
		 */
		private function hasCmd($code, $cmd)
		{
			return strpos($code, '@' . $cmd) === 0;
		}

		/**
		 * Replace @<cmd> with code
		 * 
		 * @param string $code The current code line
		 * @return string The new line with valid PHP code or the origin code if no command was found
		 */
		private function replaceCommand($code)
		{
			if ($this->hasCmd($code, 'if')) {
				return str_replace('@if', '<?php if', $code) . ' { ?>';
			} else if ($this->hasCmd($code, 'elseif')) {
				return str_replace('@elseif', '<?php } else if', $code) . ' { ?>';
			} else if ($this->hasCmd($code, 'else')) {
				return str_replace('@else', '<?php } else', $code) . ' { ?>';
			} else if ($this->hasCmd($code, 'foreach')) {
				return str_replace('@foreach', '<?php foreach', $code) . ' { ?>';
			} else if ($this->hasCmd($code, 'for')) {
				return str_replace('@for', '<?php for', $code) . ' { ?>';
			} else if ($this->hasCmd($code, 'while')) {
				return str_replace('@while', '<?php while', $code) . ' { ?>';
			} else if ($this->hasCmd($code, 'continue')) {
				return str_replace('@continue', '<?php continue; ?>', $code); 
			} else if ($this->hasCmd($code, 'do')) {
				return str_replace('@do', '<?php do', $code) . ' { ?>';
			} else if ($this->hasCmd($code, 'dwhile')) {
				return str_replace('@dwhile', '<?php } while', $code) . '; ?>';
			} else if ($this->hasCmd($code, 'switch')) {
				return str_replace('@switch', '<?php switch', $code) . ' { ?>';
			} else if ($this->hasCmd($code, 'case')) {
				return str_replace('@case', '<?php case', $code) . ': ?>';
			} else if ($this->hasCmd($code, 'break')) {
				return str_replace('@break', '<?php break; ?>', $code); 
			} else if ($this->hasCmd($code, 'default')) {
				return str_replace('@default', '<?php default: ?>', $code);
			} else if ($this->hasCmd($code, 'endif')) {
				return str_replace('@endif', '<?php } ?>', $code);
			} else if ($this->hasCmd($code, 'endforeach')) {
				return str_replace('@endforeach', '<?php } ?>', $code);
			} else if ($this->hasCmd($code, 'endfor')) {
				return str_replace('@endfor', '<?php } ?>', $code);
			} else if ($this->hasCmd($code, 'endwhile')) {
				return str_replace('@endwhile', '<?php } ?>', $code);
			} else if ($this->hasCmd($code, 'endswitch')) {
				return str_replace('@endswitch', '<?php } ?>', $code);
			} else if ($this->hasCmd($code, 'csrf')) {
				return str_replace('@csrf', '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '"/>', $code);
			} else if ($this->hasCmd($code, 'comment')) {
				return str_replace('@comment', '<?php /*', $code) . ' */ ?>';
			} else if ($this->hasCmd($code, 'include')) {
				$fileToInclude = substr($code, strpos($code, ' ') + 1);
				$fileToInclude = str_replace('"', '', $fileToInclude);
				$fileToInclude = str_replace('\'', '', $fileToInclude);
				$cont = $this->renderCode(file_get_contents(app_path() . '/views/' . $fileToInclude));
				return str_replace($code, $cont, $code);
			} else if ($this->hasCmd($code, 'isset')) {
				return str_replace('@isset', '<?php if (isset(', $code) . ')) { ?>';
			} else if ($this->hasCmd($code, 'isnotset')) {
				return str_replace('@isnotset', '<?php if (!isset(', $code) . ')) { ?>';
			} else if ($this->hasCmd($code, 'endset')) {
				return str_replace('@endset', '<?php } ?>', $code);
			}

			return $code;
		}

		/**
		 * Indicate if mustache syntax shall be ignored
		 * 
		 * @param string $elem The current element
		 * @param int $i The start index of element to be checked
		 * @return boolean
		 */
		private function shallIgnoreMustache($elem, $i)
		{
			if (($i > 0) && ($elem[$i - 1] === '@')) {
				return true;
			}

			return false;
		}

		/**
		 * Return rendered code
		 * 
		 * @param string $code The actual view content
		 * @return string The rendered view content
		 */
		private function renderCode($code)
		{
			//This array will contain all parsed lines
			$resultCode = array();
			
			//Split into array with lines
			$elems = preg_split("/\r\n|\n|\r/", $code);
			foreach ($elems as $elem) { //Loop through every line
				$hadNotSpaceChar = false; //Indicator whether we had encountered a space char before
				
				//At first replace all template command identifiers
				for ($i = 0; $i < strlen($elem); $i++) {
					//Replace tab chars with spaces
					$elem = preg_replace('/\t+/', ' ', $elem);
					
					//If before a template command start token are other chars then don't continue with parsing it
					if (($elem[$i] !== ' ') && (!$hadNotSpaceChar)) {
						if ($elem[$i] !== '@') {
							$hadNotSpaceChar = true;
						}
					}
					
					//If command identifier token is found and is not prevented then replace the code
					if (($elem[$i] === '@') && (!$hadNotSpaceChar)) {
						$elem = $this->replaceCommand(substr($elem, $i));
					}
				}

				//Next replace all mustache sequences
				$bInPhpCode = false;
				for ($i = 0; $i < strlen($elem); $i++) {
					//Check if we are current in PHP code scope
					if ((substr($elem, $i, 5) === '<?php') || (substr($elem, $i, 3) === '<?=')) {
						if (!$bInPhpCode)
							$bInPhpCode = true;
					}

					//Check if not anymore in PHP code scope
					if ((substr($elem, $i, 2) === '?>') && ($bInPhpCode)) {
						$bInPhpCode = false;
					}

					//Check for mustache
					if (($elem[$i] === '{') && (($i + 1 < strlen($elem)) && ($elem[$i + 1] === '{'))) {
						$endOfSeq = strlen($elem);
						
						//Find end of sequence
						for ($j = $i + 2; $j < strlen($elem); $j++) {
							if (($elem[$j] === '}') && (($j + 1 < strlen($elem)) && ($elem[$j + 1] === '}'))) {
								$endOfSeq = $j;
								break;
							}
						}
						
						//Create a new line with the replaced template token with PHP code
						if (!$this->shallIgnoreMustache($elem, $i)) { //The mustache syntax shall be handled
							$elem = substr($elem, 0, $i) . '<?= htmlspecialchars(' . substr($elem, $i + 2, $endOfSeq - $i - 2) . ', ENT_QUOTES | ENT_HTML401); ?>' . substr($elem, $endOfSeq + 2);
						} else { //The mustache syntax shall be ignored
							$elem = substr($elem, 0, $i - 1) . substr($elem, $i);
						}
					}
				}

				//Add current line to array
				array_push($resultCode, $elem);
			}
			
			//Return the code which shall be rendered
			return implode(PHP_EOL, $resultCode);
		}
		
		/**
		 * Render the view with given information
		 * 
		 * @param boolean $return optional Wether the view shall be rendered or its content returned
		 * @return mixed
		 */
		public function out($return = false)
		{
			//Create all variables
			foreach ($this->vars as $key => $value) {
				$$key = $value;
			}
			
			//Acquire content of the layout file
			$layout = file_get_contents(__DIR__ . '/../../../../app/views/' . $this->layout . '.php');
			
			//Replace all yields
			foreach ($this->yields as $key => $value) {
				$layout = str_replace('{%' . $key . '%}', $value, $layout);
			}
			
			//Render the code or return depending on flag
			$result = true;
			if (!$return) {
				eval('?>' . $this->renderCode($layout));
			} else {
				ob_start();
				eval('?>' . $this->renderCode($layout));
				$result = ob_get_contents();
				ob_end_clean();
			}

			//Remove all variables
			foreach ($this->vars as $key => $value) {
				unset($$key);
			}

			return $result;
		}
	}

	/**
	 * This component handles a json result
	 */
	class JsonHandler implements ViewInterface {
		private $content = null;

		/**
		 * Construct with content
		 * 
		 * @param string $content The content to be handled as json
		 * @return void
		 */
		public function __construct($content = '')
		{
			$this->content = $content;
		}

		/**
		 * Render the content as json
		 * 
		 * @return mixed
		 */
		public function out($return = false)
		{
			if ($return === false) {
				header('Content-type: application/json');
				echo json_encode($this->content);
				return true;
			} else {
				return json_encode($this->content);
			}
		}
	}

	/**
	 * This component handles an xml result
	 */
	class XmlHandler implements ViewInterface {
		private $content = null;

		/**
		 * Construct with content
		 * 
		 * @param string $content The content to be handled as XML
		 * @return void
		 */
		public function __construct($content = '')
		{
			$this->content = $content;
		}

		/**
		 * Render the content as xml
		 * 
		 * @return mixed
		 */
		public function out($return = false)
		{
			if ($return === false) {
				header('Content-type: text/xml');
				echo $this->content;
				return true;
			} else {
				return $this->content;
			}
		}
	}

	/**
	 * This component handles a plain result
	 */
	class PlainHandler implements ViewInterface {
		private $content = null;

		/**
		 * Construct with content
		 * 
		 * @param string $content The content to be handled as plain text
		 * @return void
		 */
		public function __construct($content = '')
		{
			//Set content

			$this->content = $content;
		}

		/**
		 * Render the content as plain text
		 * 
		 * @return mixed
		 */
		public function out($return = false)
		{
			if ($return === false) {
				header('Content-type: text/plain');
				echo $this->content;
				return true;
			} else {
				return $this->content;
			}
		}
	}

	/**
	 * This components handles a redirect
	 */
	class RedirectHandler implements ViewInterface {
		private $dest = null;

		/**
		 * Set URL
		 * 
		 * @param string $url The URL, either internal or external
		 * @return void
		 */
		public function __construct($url = '/')
		{
			$this->dest = $url;
		}

		/**
		 * Redirect to url
		 * 
		 * @return void
		 */
		public function out()
		{
			header('Location: ' . $this->dest);
			exit();
		}
	}

	/**
	 * This components handles a download
	 */
	class DownloadHandler implements ViewInterface {
		private $resource = null;

		/**
		 * Set resource
		 * 
		 * @param string $res URL to the resource
		 * @return void
		 */
		public function __construct($res)
		{
			$this->resource = $res;
		}

		/**
		 * Redirect to resource
		 * 
		 * @return void
		 */
		public function out()
		{
			header('Location: ' . (((isset($_ENV['APP_BASEDIR']) && strlen($_ENV['APP_BASEDIR']) > 0)) ? '/' . $_ENV['APP_BASEDIR']  : '') . $this->resource);
			exit();
		}
	}

	/**
	 * This components handles a custom type
	 */
	class CustomHandler implements ViewInterface {
		private $type = null;
		private $content = null;

		/**
		 * Set data
		 * 
		 * @param string $type The content type
		 * @param string $content The actual content
		 * @return void
		 */
		public function __construct($type, $content)
		{
			$this->type = $type;
			$this->content = $content;
		}

		/**
		 * Render data
		 * 
		 * @return mixed
		 */
		public function out($return = false)
		{
			if ($return === false) {
				header('Content-type: ' . $this->type);
				echo $this->content;
				return true;
			} else {
				return $this->content;
			}
		}
	}
}

namespace {
	/**
	 * Handle a result of a controller method
	 * 
	 * @param mixed $result The result from the controller method
	 * @return void
	 * @throws \Exception
	 */
	function HandleView($result)
	{
		if (gettype($result) == 'string') { //Default output is plain html
			echo $result;
		} else if (gettype($result) == 'object') { //Handle interface class
			$implData = class_implements($result);
			if (isset($implData['Asatru\View\ViewInterface'])) {
				$result->out();
			}
		} else { //No handler for this type of data
			throw new Exception('Unknow data type for view rendering: ' . gettype($result) . ' (' . ((gettype($result) == 'object') ? get_class($result) : 'null') . ')');
		}
	}
}