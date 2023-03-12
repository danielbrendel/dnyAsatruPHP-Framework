<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2023 by Daniel Brendel
    
    Version: 1.0
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace Asatru\SMTPMailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Component for SMTP based mailing
 */
class SMTPMailer
{
    private $to;
    private $subject;
    private $message;
    private $fromName;
    private $fromAddress;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fromName = env_get('SMTP_FROMNAME');
        $this->fromAddress = env_get('SMTP_FROMADDRESS');
    }

    /**
     * Set recipient of the E-Mail
     * 
     * @param string $value The recipients E-Mail address
     * @return Asatru\SMTPMailer\SMTPMailer
     */
    public function setRecipient($value)
    {
        $this->to = $value;

        return $this;
    }

    /**
     * Set subject of the E-Mail
     * 
     * @param string $value The subject
     * @return Asatru\SMTPMailer\SMTPMailer
     */
    public function setSubject($value)
    {
        $this->subject = $value;

        return $this;
    }

    /**
     * Set message content
     * 
     * @param string $content The message content
     * @return Asatru\SMTPMailer\SMTPMailer
     */
    public function setMessage($content)
    {
        $this->message = $content;

        return $this;
    }

    /**
     * Render the view of the E-Mail
     * 
     * @param string $layout The layout file
     * @param array $yields The used yields
     * @param array $data optional The variables if any
     * @return Asatru\SMTPMailer\SMTPMailer
     */
    public function setView($layout, array $yields, array $data = [])
    {
        $this->message = view($layout, $yields, $data)->out(true);

        return $this;
    }

    /**
     * Send mail using PHPMailer
     * 
     * @throws Exception
     */
    public function send()
    {
        try {
            $mail = new PHPMailer(true);

            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host = env_get('SMTP_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env_get('SMTP_USERNAME');
            $mail->Password = env_get('SMTP_PASSWORD');
            if (env_get('SMTP_ENCRYPTION') === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else if (env_get('SMTP_ENCRYPTION') === 'smtps') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                throw new Exception('Unknown encryption type: ' . env_get('SMTP_ENCRYPTION'));
            }
            $mail->Port       = env_get('SMTP_PORT');

            //Recipients
            $mail->setFrom($this->fromAddress, $this->fromName);
            $mail->addAddress($this->to);
            $mail->addReplyTo($this->fromAddress, $this->fromName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $this->subject;
            $mail->Body = $this->message;

            $mail->send();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
