<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2021 by Daniel Brendel
    
    Version: 1.0
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

use PHPUnit\Framework\TestCase;

/**
 * TestCase for Asatru\Logger
 */
final class LoggerTest extends TestCase
{
    protected static function getMethod($name)
    {
        $class = new ReflectionClass('Asatru\\Logger\\Logger');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testLogger()
    {
        $logger = new Asatru\Logger\Logger();

        $method = self::getMethod('getStrType');
        $result = $method->invokeArgs($logger, array(ASATRU_LOG_HEADER));
        $this->assertEquals('Header', $result);
        $result = $method->invokeArgs($logger, array(ASATRU_LOG_INFO));
        $this->assertEquals('Info', $result);
        $result = $method->invokeArgs($logger, array(ASATRU_LOG_DEBUG));
        $this->assertEquals('Debug', $result);
        $result = $method->invokeArgs($logger, array(ASATRU_LOG_WARNING));
        $this->assertEquals('Warning', $result);
        $result = $method->invokeArgs($logger, array(ASATRU_LOG_ERROR));
        $this->assertEquals('Error', $result);
        $result = $method->invokeArgs($logger, array(5));
        $this->assertEquals('Unknown', $result);

        $logger->add(ASATRU_LOG_INFO, 'TestCase Info');
        $logger->add(ASATRU_LOG_DEBUG, 'TestCase Debug');
        $logger->add(ASATRU_LOG_WARNING, 'TestCase Warning');
        $logger->add(ASATRU_LOG_ERROR, 'TestCase Error');
        $logger->store();
        $logger->clear();

        $this->addToAssertionCount(7);
    }

    public function testLogging()
    {
        addLog(ASATRU_LOG_INFO, 'TestCase Info');
        addLog(ASATRU_LOG_DEBUG, 'TestCase Debug');
        addLog(ASATRU_LOG_WARNING, 'TestCase Warning');
        addLog(ASATRU_LOG_ERROR, 'TestCase Error');
        storeLog();
        clearLog();

        $this->addToAssertionCount(5);
    }
}