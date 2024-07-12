<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2024 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace Asatru\Testing;

use PHPUnit\Framework\TestCase;

/**
 * This components provides extended testing methods
 */
class Test extends TestCase {
    protected $ctrlresult = null;

    /** HTTP Request methods */
    const REQUEST_GET = 'GET';
    const REQUEST_POST = 'POST';
    const REQUEST_PUT = 'PUT';
    const REQUEST_PATCH = 'PATCH';
    const REQUEST_DELETE = 'DELETE';

    /**
     * Method to simulate a request on a route
     * 
     * @param string $method The request method
     * @param string $route The route as it would be typed in the browser
     * @param array $data An array containing key-value pair arrays for POST or GET data
     * @return Asatru\Testing\Test The class instance of this test
     */
    public function request($method, $route, $data = array())
    {
        //Get URL from environment
        $server_url = env('APP_URL', 'http://localhost:8000');

        //Parse URL
        $parsed_url = parse_url($server_url . $route);

        //Set server variables
        $_SERVER['REQUEST_METHOD'] = strtolower($method);
        $_SERVER['REQUEST_URI'] = $server_url . $route;
        $_SERVER['SERVER_NAME'] = $parsed_url['host'];
        $_SERVER['SERVER_PORT'] = $parsed_url['port'] ?? 80;
        $_SERVER['HTTPS'] = ((isset($parsed_url['scheme'])) && ($parsed_url['scheme'] === 'https')) ? 'on' : 'off';

        //Parse query parameters
        if (isset($parsed_url['query'])) {
            parse_str($parsed_url['query'], $outArrayParams);

            foreach ($outArrayParams as $key => $item) {
                $_GET[$key] = $item;
            }
        }

        //Create data
        if (isset($data['GET'])) {
            foreach ($data['GET'] as $key => $item) {
                $_GET[$key] = $item;
            }
        }
        if (isset($data['POST'])) {
            foreach ($data['POST'] as $key => $item) {
                $_POST[$key] = $item;
            }
        }

        //Dispatch to controller
        $controller = new \Asatru\Controller\ControllerHandler(ASATRU_APP_ROOT . '/app/config/routes.php');
        $this->ctrlresult = $controller->parse($route);

        return $this;
    }

    /**
     * Get response of previous route call
     * 
     * @return mixed Depends on the result of the controller method
     */
    public function getResponse()
    {
        return $this->ctrlresult;
    }
}