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
 * TestCase for Asatru\Dotenv
 */
final class DotenvTest extends TestCase
{
    public function testEnv()
    {
        $result = env_parse(__DIR__ . '/../../../../.env');
        $this->assertTrue(env_has_error() === false);
        $this->assertTrue(env_get('APP_NAME') === 'Asatru PHP');
    }
}