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
 * TestCase for Asatru\Console
 */
final class ConsoleTest extends TestCase
{
    public function testCreateModel()
    {
        $name = 'Testmodel';

        $result = Asatru\Console\createModel($name);
        $this->assertTrue($result);

        $scriptFile = require(__DIR__ . '/../../../../app/migrations/' . $name . '.php');
        $this->assertEquals(1, $scriptFile);

        $className = $name . '_Migration';
        $newClass = new $className(null);
        $this->assertTrue(method_exists($newClass, 'up'));
        $this->assertTrue(method_exists($newClass, 'down'));

        $scriptFile = require(__DIR__ . '/../../../../app/models/' . $name . '.php');
        $this->assertEquals(1, $scriptFile);

        $newClass = new $name();
        $this->assertNotNull($newClass);

        unlink(__DIR__ . '/../../../../app/migrations/' . $name . '.php');
        unlink(__DIR__ . '/../../../../app/models/' . $name . '.php');
    }

    public function testCreateModule()
    {
        $name = 'TestModule';

        $result = Asatru\Console\createModule($name);
        $this->assertTrue($result);

        $scriptFile = require(__DIR__ . '/../../../../app/modules/' . $name . '.php');
        $this->assertEquals(1, $scriptFile);

        $className = $name;
        $newClass = new $className();
        $this->assertIsObject($newClass);
        $this->assertInstanceOf($className, $newClass);

        unlink(__DIR__ . '/../../../../app/modules/' . $name . '.php');
    }

    public function testCreateController()
    {
        $name = 'Testcontrollercreation';

        $result = Asatru\Console\createController($name);
        $this->assertTrue($result);

        $scriptFile = require(__DIR__ . '/../../../../app/controller/' . $name . '.php');
        $this->assertEquals(1, $scriptFile);

        $className = $name . 'Controller';
        $newClass = new $className();
        $this->addToAssertionCount(1);

        unlink(__DIR__ . '/../../../../app/controller/' . $name . '.php');
    }

    public function testCreateLang()
    {
        $ident = 'de';

        $result = Asatru\Console\createLang($ident);
        $this->assertTrue($result);

        $scriptReturn = require(__DIR__ . '/../../../../app/lang/' . $ident . '/app.php');
        $this->assertIsArray($scriptReturn);

        unlink(__DIR__ . '/../../../../app/lang/' . $ident . '/app.php');
        rmdir(__DIR__ . '/../../../../app/lang/' . $ident);
    }

    public function testCreateValidator()
    {
        $name = 'Testvalidatorcreation';
        $ident = 'testcase';

        $result = Asatru\Console\createValidator($name, $ident);
        $this->assertTrue($result);

        $scriptFile = require(__DIR__ . '/../../../../app/validators/' . $name . '.php');
        $this->assertEquals(1, $scriptFile);

        $className = $name . 'Validator';
        $newClass = new $className();
        $this->assertTrue(method_exists($newClass, 'getIdent'));
        $this->assertTrue(method_exists($newClass, 'verify'));
        $this->assertTrue(method_exists($newClass, 'getError'));
        $this->assertEquals($ident, $newClass->getIdent());
        $this->assertTrue($newClass->verify(null));
        $this->assertEquals(null, $newClass->getError());

        unlink(__DIR__ . '/../../../../app/validators/' . $name . '.php');
    }

    public function testCreateAuth()
    {
        $name = 'Auth';
        $migration = 'Auth';

        $result = Asatru\Console\createAuth();
        $this->assertTrue($result);

        $scriptFile = require(__DIR__ . '/../../../../app/migrations/' . $name . '.php');
        $this->assertEquals(1, $scriptFile);

        $className = $name . '_Migration';
        $newClass = new $className(null);
        $this->assertTrue(method_exists($newClass, 'up'));
        $this->assertTrue(method_exists($newClass, 'down'));

        $scriptFile = require(__DIR__ . '/../../../../app/models/' . $name . '.php');
        $this->assertEquals(1, $scriptFile);

        $newClass = new $name();
        $this->assertTrue(method_exists($newClass, 'register'));
        $this->assertTrue(method_exists($newClass, 'login'));
        $this->assertTrue(method_exists($newClass, 'logout'));
        $this->assertTrue(method_exists($newClass, 'isUserLoggedIn'));
        $this->assertTrue(method_exists($newClass, 'getByEmail'));
        $this->assertTrue(method_exists($newClass, 'getBySession'));
        $this->assertTrue(method_exists($newClass, 'tableName'));
        $this->assertEquals($migration, $newClass->tableName());

        unlink(__DIR__ . '/../../../../app/migrations/' . $name . '.php');
        unlink(__DIR__ . '/../../../../app/models/' . $name . '.php');
    }

    public function testCreateTest()
    {
        $name = 'Testtestcreation';

        $result = Asatru\Console\createTest($name);
        $this->assertTrue($result);

        $this->assertTrue(file_exists(__DIR__ . '/../../../../app/tests/bootstrap.php'));

        $scriptFile = require(__DIR__ . '/../../../../app/tests/' . $name . 'Test.php');
        $this->assertEquals(1, $scriptFile);

        $className = $name . 'Test';
        $newClass = new $className();
        $this->addToAssertionCount(1);

        unlink(__DIR__ . '/../../../../app/tests/' . $name . 'Test.php');
    }
}