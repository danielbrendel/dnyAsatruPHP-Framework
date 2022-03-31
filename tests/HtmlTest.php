<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2022 by Daniel Brendel
    
    Version: 1.0
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

use PHPUnit\Framework\TestCase;

/**
 * TestCase for Html helper class
 */
final class HtmlTest extends TestCase
{
    public function testRenderTag()
    {
        $result = Html::renderTag('img', [
            'src' => 'test.png',
            'class' => 'my-test-class',
            'alt' => 'Alternative image text'
        ]);

        $this->assertEquals('<img src="test.png" class="my-test-class" alt="Alternative image text">', $result);
    }

    public function testRenderCloseTag()
    {
        $result = Html::renderCloseTag('a');

        $this->assertEquals('</a>', $result);
    }
}