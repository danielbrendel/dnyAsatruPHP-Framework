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
 * TestCase for Asatru\Logger
 */
final class LoggerTest extends TestCase
{
    public function testLogging()
    {
        addLog(LOG_INFO, 'TestCase');
        addLog(LOG_INFO, 'TestCase');
        addLog(LOG_INFO, 'TestCase');
        storeLog();
        clearLog();

        $this->addToAssertionCount(5);
    }
}