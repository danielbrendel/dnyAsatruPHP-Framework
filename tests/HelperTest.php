<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
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

    public function testBasePath()
    {
        $result = base_path();
        $this->assertTrue(is_dir($result));
    }

    public function testAppPath()
    {
        $result = app_path();
        $this->assertTrue(is_dir($result));
    }

    public function testResourcePath()
    {
        $result = resource_path();
        $this->assertTrue(is_dir($result));
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

    public function testUrl()
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;
        $_ENV['APP_BASEDIR'] = '/testdir';

        $result = url('/path/to/resource');

        $this->assertEquals('http://localhost/testdir/path/to/resource', $result);
    }

    public function testAppUrl()
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 8000;
        $_ENV['APP_BASEDIR'] = '/testdir';

        $result = app_url();

        $this->assertEquals('https://localhost:8000/testdir/app', $result);
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

    public function testAsset()
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 8000;
        $_ENV['APP_BASEDIR'] = '/testdir';

        $result = asset('/js/app.js');

        $this->assertEquals('https://localhost:8000/testdir/app/resources/js/app.js', $result);
    }

    public function testCsrfToken()
    {
        $_SESSION['csrf_token'] = 'Hello World';

        $this->assertEquals($_SESSION['csrf_token'], csrf_token());
    }

    public function testTemplateCommand()
    {
        $result = template_command('sometest', function(string $code, array $args){ return '<?php echo "Just a test"; ?>';});
        $this->assertTrue($result);
    }
}