<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

namespace Asatru\Testing;

use PHPUnit\Framework\TestCase;

//This components provides extended testing methods
class Test extends TestCase {
    protected $ctrlresult = null;

    public function route($method, $route, $data = array())
    {
        //Method to simulate a request on a route

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
        $controller = new \Asatru\Controller\ControllerHandler(__DIR__ . '/../../../../app/config/routes.php');
        $this->ctrlresult = $controller->parse($route);
    }

    public function getResponse()
    {
        //Get response of previous route call

        return $this->ctrlresult;
    }
}