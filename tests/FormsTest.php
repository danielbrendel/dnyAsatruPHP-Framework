<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2025 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

use PHPUnit\Framework\TestCase;

/**
 * TestCase for Form helper class
 */
final class FormsTest extends TestCase
{
    public function testBegin()
    {
        $result = Form::begin([
            'method' => 'POST',
            'action' => 'my/test/action'
        ]);

        $this->assertEquals('<form method="POST" action="my/test/action">', $result);
    }

    public function testPutElement()
    {
        $result = Form::putElement('textarea', [
            'name' => 'my-textarea',
            'class' => 'class1 class2 class3',
            'required' => null
        ]);

        $this->assertEquals('<textarea name="my-textarea" class="class1 class2 class3" required>', $result);
    }

    public function testCloseElement()
    {
        $result = Form::closeElement('textarea');

        $this->assertEquals('</textarea>', $result);
    }

    public function testEnd()
    {
        $result = Form::end();

        $this->assertEquals('</form>', $result);
    }
}