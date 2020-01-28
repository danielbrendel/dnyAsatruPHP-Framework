<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

use PHPUnit\Framework\TestCase;

/**
 * TestCase for Asatru\Testing
 */
final class TestingTest extends TestCase
{
    public function testTestingClass()
    {
        $testing = new Asatru\Testing\Test();
        $testing->route('GET', '/test/1/another/2', array());
        $this->addToAssertionCount(1);
        $result = $testing->getResponse();
        $this->assertInstanceOf('Asatru\\View\ViewHandler', $result);
    }
}