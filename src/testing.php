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

    /**
     * Method to simulate a request on a route
     * 
     * @param string $method The request method
     * @param string $route The route as it would be typed in the browser
     * @param array $data An array containing key-value pair arrays for POST or GET data
     * @return void
     */
    public function route($method, $route, $data = array())
    {
        //Set method
        $_SERVER['REQUEST_METHOD'] = $method;

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