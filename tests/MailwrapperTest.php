<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2023 by Daniel Brendel
    
    Version: 1.0
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

use PHPUnit\Framework\TestCase;

/**
 * TestCase for Asatru\Mailwrapper
 */
final class MailwrapperTest extends TestCase
{
    public function testMailwrapper()
    {
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;

        $mail = new Asatru\Mailwrapper\Mail();
        $mail->setRecipient('test@test.de');
        $mail->setSubject('TestCase');
        $mail->setView('layout', array(array('yield', 'index')), array());
        $mail->setAdditionalHeaders(array('X-Mailer' => 'PHP/' . phpversion()));
        $mail->setAdditionalParameters('-ftest@test.de');
        //$mail->send();
        $this->addToAssertionCount(1);
    }
}