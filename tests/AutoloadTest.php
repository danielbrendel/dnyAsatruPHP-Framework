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
 * TestCase for Asatru\Autoload
 */
final class AutoloadTest extends TestCase
{
    public function testAutoload()
    {
        $autoloader = new Asatru\Autoload\Autoloader();
        $autoloader->load();
        $this->addToAssertionCount(2);
    }
}