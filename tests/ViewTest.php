<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2024 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
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
        $result = $method->invokeArgs($vh, array('@if ($a == $b)'));
        $this->assertEquals('<?php if ($a == $b) { ?>', $result);
        $result = $method->invokeArgs($vh, array('@elseif ($a != $b)'));
        $this->assertEquals('<?php } else if ($a != $b) { ?>', $result);
        $result = $method->invokeArgs($vh, array('@else'));
        $this->assertEquals('<?php } else { ?>', $result);
        $result = $method->invokeArgs($vh, array('@endif'));
        $this->assertEquals('<?php } ?>', $result);
        $result = $method->invokeArgs($vh, array('@foreach ($a as $key => $value)'));
        $this->assertEquals('<?php foreach ($a as $key => $value) { ?>', $result);
        $result = $method->invokeArgs($vh, array('@for ($i = 0; $i < 10; $i++)'));
        $this->assertEquals('<?php for ($i = 0; $i < 10; $i++) { ?>', $result);
        $result = $method->invokeArgs($vh, array('@while ($a < 10)'));
        $this->assertEquals('<?php while ($a < 10) { ?>', $result);
        $result = $method->invokeArgs($vh, array('@continue'));
        $this->assertEquals('<?php continue; ?>', $result);
        $result = $method->invokeArgs($vh, array('@do'));
        $this->assertEquals('<?php do { ?>', $result);
        $result = $method->invokeArgs($vh, array('@dwhile ($a < 10)'));
        $this->assertEquals('<?php } while ($a < 10); ?>', $result);
        $result = $method->invokeArgs($vh, array('@switch ($a)'));
        $this->assertEquals('<?php switch ($a) { ?>', $result);
        $result = $method->invokeArgs($vh, array('@case true'));
        $this->assertEquals('<?php case true: ?>', $result);
        $result = $method->invokeArgs($vh, array('@break'));
        $this->assertEquals('<?php break; ?>', $result);
        $result = $method->invokeArgs($vh, array('@default'));
        $this->assertEquals('<?php default: ?>', $result);
        $result = $method->invokeArgs($vh, array('@endif'));
        $this->assertEquals('<?php } ?>', $result);
        $result = $method->invokeArgs($vh, array('@endforeach'));
        $this->assertEquals('<?php } ?>', $result);
        $result = $method->invokeArgs($vh, array('@endfor'));
        $this->assertEquals('<?php } ?>', $result);
        $result = $method->invokeArgs($vh, array('@endwhile'));
        $this->assertEquals('<?php } ?>', $result);
        $result = $method->invokeArgs($vh, array('@endswitch'));
        $this->assertEquals('<?php } ?>', $result);
        $_SESSION['csrf_token'] = 'TestCase';
        $result = $method->invokeArgs($vh, array('@csrf'));
        $this->assertEquals('<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '"/>', $result);
		$result = $method->invokeArgs($vh, array('@method("PATCH")'));
		$this->assertEquals('<input type="hidden" name="_method" value="PATCH"/>', $result);
        $result = $method->invokeArgs($vh, array('@comment From a TestCase'));
        $this->assertEquals('<?php /* From a TestCase */ ?>', $result);
        $result = $method->invokeArgs($vh, array('@include("index.php")'));
        $this->assertTrue(strpos($result, '<div class="outer">') !== false);
        $this->assertTrue(strpos($result, '<?= htmlspecialchars( __(\'app.welcome\') , ENT_QUOTES | ENT_HTML401); ?>') !== false);
        $result = $method->invokeArgs($vh, array('@isset $a'));
        $this->assertEquals('<?php if (isset( $a)) { ?>', $result);
        $result = $method->invokeArgs($vh, array('@isnotset $a'));
        $this->assertEquals('<?php if (!isset( $a)) { ?>', $result);
        $result = $method->invokeArgs($vh, array('@endset'));
        $this->assertEquals('<?php } ?>', $result);
        $result = $method->invokeArgs($vh, array('@unknowncommand'));
        $this->assertEquals('@unknowncommand', $result);

        template_command('test1', function(string $code, array $args){ return '<?php echo "Just a test"; ?>';});
        template_command('test2', function(string $code, array $args){ return str_replace('@test2', '<?php if (gettype', $code) . '==="string") { ?>';});
        $this->addToAssertionCount(2);

        $result = $method->invokeArgs($vh, array('@test1'));
        $this->assertEquals('<?php echo "Just a test"; ?>', $result);

        $result = $method->invokeArgs($vh, array('@test2("Hello World!")'));
        $this->assertEquals('<?php if (gettype("Hello World!")==="string") { ?>', $result);
    }

    public function testViewHandlerStatic()
    {
        $method = new ReflectionMethod('Asatru\\View\\ViewHandler', 'addReplacerCommand');
        $result = $method->invoke(null, 'testcase', function(string $code, array $args){return '<?php echo "TestCase"; ?>';});
        $this->assertTrue($result);
        $method = self::getMethod('getReplacerCommand');
        $result = $method->invoke(null, 'notfound');
        $this->assertTrue($result === null);
        $result = $method->invoke(null, 'testcase');
        $this->assertTrue($result !== null);
        $this->assertTrue(gettype($result) === 'object');
        $method = self::getMethod('parseReplacerCommandParams');
        $result = $method->invoke(null, '("Hello World!", 1020, true)');
        $this->assertIsArray($result);
        $this->assertEquals(3, count($result));
        $this->assertEquals('Hello World!', $result[0]);
        $this->assertEquals(1020, $result[1]);
        $this->assertEquals(true, $result[2]);
    }

    public function testJsonHandler()
    {
        $jh = new Asatru\View\JsonHandler(array('key' => 'value'));
    
        $result = $jh->out(true);
        $this->assertEquals('{"key":"value"}', $result);
    }

    public function testXmlHandler()
    {
        $code = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . '<data><key>value</key></data>' . "\n";

        $xmlh = new Asatru\View\XmlHandler(array('key' => 'value'));
    
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