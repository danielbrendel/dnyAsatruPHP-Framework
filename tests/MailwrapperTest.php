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
 * TestCase for Asatru\Mailwrapper
 */
final class MailwrapperTest extends TestCase
{
    public function testMailwrapper()
    {
        $this->markTestSkipped('For local tests only');

        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;

        $mail = new Asatru\Mailwrapper\Mail();
        $mail->setRecipient('test@test.de');
        $mail->setSubject('TestCase');
        $mail->setView('layout', array('yield' => 'index'), array());
        $mail->send();
    }
}