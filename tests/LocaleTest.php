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
 * TestCase for Asatru\Lang
 */
final class LocaleTest extends TestCase
{
    public function testLocale()
    {
        $result = getLocale();
        $this->assertEquals('en', $result);

        $result = __('app.welcome');
        $this->assertTrue($result !== 'app.welcome');
    }

    public function testLanguage()
    {
        $lang = new Asatru\Lang\Language();
        $lang->load('en');
        $this->addToAssertionCount(2);

        $result = $lang->query('app.welcome');
        $this->assertTrue($result !== 'app.welcome');
    }
}