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
 * TestCase for Asatru\View
 */
final class ViewTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;
    }

    protected static function getMethod($name)
    {
        $class = new ReflectionClass('Asatru\\View\\ViewHandler');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testViewHandler()
    {
        $vh = new Asatru\View\ViewHandler();
        $vh->setLayout('layout');
        $vh->setYield('yield', 'index');
        $vh->setVars(array('some_var' => 'value'));
        $result = $vh->out(true);
        $this->assertTrue(strpos($result, '<meta name="viewport" content="width=device-with, initial-scale=1.0">') !== false);
        $this->assertTrue(strpos($result, 'Example yield file') !== false);
    }

    public function testViewHandlerReplace()
    {
        $method = self::getMethod('replaceCommand');
        $vh = new Asatru\View\ViewHandler();
        $result = $method->invokeArgs($vh, array('@dwhile ($a < 10)'));
        $this->assertEquals('<?php while ($a < 10); ?>', $result);
    }

    public function testJsonHandler()
    {
        $jh = new Asatru\View\JsonHandler(array('foo' => 'bar'));
    
        $result = $jh->out(true);
        $this->assertEquals('{"foo":"bar"}', $result);
    }

    public function testXmlHandler()
    {
        $code = '<test><foo>Hallo</foo></test>';

        $xmlh = new Asatru\View\XmlHandler($code);
    
        $result = $xmlh->out(true);
        $this->assertEquals($code, $result);
    }

    public function testPlainHandler()
    {
        $code = 'Test from TestCase';

        $plh = new Asatru\View\PlainHandler($code);
    
        $result = $plh->out(true);
        $this->assertEquals($code, $result);
    }
}