<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

namespace Asatru\View {
	//Basic view interface
	interface ViewInterface {
		public function out();
	}

	//This component handles the view rendering
	class ViewHandler implements ViewInterface {
		private $yields = [];
		private $layout = null;
		private $vars = [];
		
		public function __construct($vars = [])
		{
			//Instantiate object

			//To ease usage we can pass the variable array if any
			$this->vars = $vars;
		}
		
		public function setLayout($layout)
		{
			//Set the layout file

			$this->layout = $layout;
			
			return $this;
		}
		
		public function setYield($name, $file)
		{
			//Add a yield

			$this->yields[$name] = file_get_contents(__DIR__ . '/../../../../app/views/' . $file . '.php');;
			
			return $this;
		}
		
		public function setVars($vars)
		{
			//Set view variables

			$this->vars = $vars;
			
			return $this;
		}

		private function hasCmd($code, $cmd)
		{
			//Whether command is there

			return strpos($code, '@' . $cmd) === 0;
		}

		private function replaceCommand($code)
		{
			//Replace @<cmd> with code

			if ($this->hasCmd($code, 'if')) {
				return str_replace('@if', '<?php if', $code) . ' { ?>';
			} else if ($this->hasCmd($code, 'elseif')) {
				return str_replace('@elseif', '<?php } elseif', $code) . ' { ?>';
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
			} else if ($this->hasCmd($code, 'switch')) {
				return str_replace('@switch', '<?php switch', $code) . ' { ?>';
			} else if ($this->hasCmd($code, 'case')) {
				return str_replace('@case', '<?php case', $code) . ' : ?>';
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
				return str_replace('@comment', '<?php /* ', $code) . ' */ ?>';
			} else if ($this->hasCmd($code, 'include')) {
				$fileToInclude = substr($code, strpos($code, ' ') + 1);
				$fileToInclude = str_replace('"', '', $fileToInclude);
				$fileToInclude = str_replace('\'', '', $fileToInclude);
				$cont = $this->renderCode(file_get_contents(\Asatru\Helper\app_path() . '/views/' . $fileToInclude));
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

		private function shallIgnoreMustache($elem, $i)
		{
			//Indicate if mustache syntax shall be ignored

			if (($i > 0) && ($elem[$i - 1] === '@')) {
				return true;
			}

			return false;
		}

		private function renderCode($code)
		{
			//Return rendered code

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
		
		public function out()
		{
			//Render the view with given information

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
			
			//Render the code
			eval('?>' . $this->renderCode($layout));
			
			//Remove all variables
			foreach ($this->vars as $key => $value) {
				unset($$key);
			}
		}
	}

	//This component handles a json result
	class JsonHandler implements ViewInterface {
		private $content = null;

		public function __construct($content = '')
		{
			//Construct with content

			$this->content = $content;
		}

		public function out()
		{
			//Render the content as json

			header('Content-type: application/json');

			echo json_encode($this->content);
		}
	}

	//This component handles an xml result
	class XmlHandler implements ViewInterface {
		private $content = null;

		public function __construct($content = '')
		{
			//Construct with content

			$this->content = $content;
		}

		public function out()
		{
			//Render the content as json

			header('Content-type: text/xml');

			echo $this->content;
		}
	}

	//This component handles a plain result
	class PlainHandler implements ViewInterface {
		private $content = null;

		public function __construct($content = '')
		{
			//Set content

			$this->content = $content;
		}

		public function out()
		{
			//Render the content as plain text

			header('Content-type: text/plain');

			echo $this->content;
		}
	}

	//This components handles a redirect
	class RedirectHandler implements ViewInterface {
		private $dest = null;

		public function __construct($url = '/')
		{
			//Set URL

			$this->dest = $url;
		}

		public function out()
		{
			//Redirect to url

			header('Location: ' . $this->dest);
			exit();
		}
	}

	//This components handles a download
	class DownloadHandler implements ViewInterface {
		private $resource = null;

		public function __construct($res)
		{
			//Set resource

			$this->resource = $res;
		}

		public function out()
		{
			//Redirect to resource

			header('Location: ' . (((isset($_ENV['APP_BASEDIR']) && strlen($_ENV['APP_BASEDIR']) > 0)) ? '/' . $_ENV['APP_BASEDIR']  : '') . $this->resource);
			exit();
		}
	}

	//This components handles a custom type
	class CustomHandler implements ViewInterface {
		private $type = null;
		private $content = null;

		public function __construct($type, $content)
		{
			//Set data

			$this->type = $type;
			$this->content = $content;
		}

		public function out()
		{
			//Render data

			header('Content-type: ' . $this->type);
			echo $this->content;
		}
	}
}

namespace {
	function HandleView($result)
	{
		//Handle a result of a controller method

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