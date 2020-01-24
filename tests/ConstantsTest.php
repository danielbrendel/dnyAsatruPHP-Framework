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
 * TestCase for Constants
 */
final class ConstantsTest extends TestCase
{
    public function testConstants()
    {
        $this->assertTrue(defined('ASATRU_FW_NAME'));
        $this->assertTrue(defined('ASATRU_FW_AUTHOR'));
        $this->assertTrue(defined('ASATRU_FW_VERSION'));
        $this->assertTrue(defined('ASATRU_FW_CONTACT'));
        $this->assertTrue(defined('COOKIE_LOCALE'));
        $this->assertTrue(defined('COOKIE_DURATION'));
    }
}