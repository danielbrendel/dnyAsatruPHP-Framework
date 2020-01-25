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

    public function testBaseUrl()
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;
        $_ENV['APP_BASEDIR'] = '/testdir';

        $result = base_url();

        $this->assertEquals('http://localhost/testdir', $result);
    }

    public function testResourceUrl()
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 8000;
        $_ENV['APP_BASEDIR'] = '/testdir';

        $result = resource_url();

        $this->assertEquals('https://localhost:8000/testdir/app/resources', $result);
    }
}