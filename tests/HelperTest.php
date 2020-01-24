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
 * TestCase for helpers
 */
final class HelperTest extends TestCase
{
    public function testFlashMessenger()
    {
        $key = 'TestCase';
        $value = 'From helper test case';

        $result = FlashMessage::hasMsg($key);
        $this->assertFalse($result);

        FlashMessage::setMsg($key, $value);
        $this->assertTrue(isset($_SESSION[$key]));
        $this->assertInstanceOf('FlashMsgItem', $_SESSION[$key]);
        $this->assertEquals($value, $_SESSION[$key]->getMsg());

        $result = FlashMessage::hasMsg($key);
        $this->assertTrue($result);

        $result = FlashMessage::getMsg($key);
        $this->assertEquals($value, $result);

        FlashMessage::clearAll();
        $this->assertFalse(isset($_SESSION[$key]));
    }
}