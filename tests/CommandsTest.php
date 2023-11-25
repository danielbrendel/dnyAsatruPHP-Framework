<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2023 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

use PHPUnit\Framework\TestCase;

/**
 * TestCase for Asatru\Commands
 */
final class CommandsTest extends TestCase
{
    protected static function getMethod($name)
    {
        $class = new ReflectionClass('Asatru\\Commands\\CustomCommands');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testLoadCommands()
    {
        $cmdmgr = new Asatru\Commands\CustomCommands(__DIR__ . '/../../../../app/config/commands.php', $argv);
        $method = self::getMethod('loadCommandConfig');
        $result = $method->invokeArgs($cmdmgr, array(__DIR__ . '/../../../../app/config/commands.php'));
        $this->addToAssertionCount(1);
        $cmdmgr->handleCommand('cmd:test');
    }
}