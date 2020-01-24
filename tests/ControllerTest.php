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
 * TestCase for Asatru\Controller
 */
final class ControllerTest extends TestCase
{
    private $controller = null;

    protected function setUp(): void
    {
        $this->controller = new Asatru\Controller\ControllerHandler(__DIR__ . '/../../../../app/config/routes.php');
    }

    public function testParseUrl()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $result = $this->controller->parse('/index');
        $this->assertInstanceOf('Asatru\\View\\ViewHandler', $result);
    }

    public function testPHPArgs()
    {
        $_GET['test_get'] = 'TestCase';
        $_POST['test_post'] = 'TestCase';

        $phpargs = new Asatru\Controller\PHPArgs();
        
        $result = $phpargs->query('test_get');
        $this->assertEquals($_GET['test_get'], $result);

        $result = $phpargs->query('test_post');
        $this->assertEquals($_POST['test_post'], $result);

        $result = $phpargs->all();
        $this->assertIsArray($result);
        $this->assertTrue(count($result) === 2);
        $this->assertTrue(isset($result['test_get']));
        $this->assertTrue(isset($result['test_post']));
    }

    public function testControllerArg()
    {
        $ctrlarg = new Asatru\Controller\ControllerArg('some/test');

        $this->assertEquals(2, $ctrlarg->count());

        $ctrlarg->addArg('another', 'value');
        $this->assertEquals('value', $ctrlarg->arg('another'));
        $this->assertEquals(3, $ctrlarg->count());
    }

    public function testPostValidator()
    {
        $_SESSION['csrf_token'] = 'TestCase';

        $_POST['test_text'] = 'hello world!';
        $_POST['test_email'] = 'test@test.de';
        $_POST['test_number'] = '1000';
        $_POST['test_datetime'] = '24.01.2020';
        $_POST['csrf_token'] = $_SESSION['csrf_token'];

        $attribs = [
            'test_text' => 'required|min:3|max:50',
            'test_email' => 'required|email',
            'test_number' => 'required|number',
            'test_datetime' => 'required|datetime:d.m.Y',
        ];
        
        $pv = new Asatru\Controller\PostValidator($attribs);
        $this->assertTrue($pv->isValid());
    }
}