<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2023 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace Asatru\Mailwrapper;

/**
 * This components handles the mail() wrapping
 */
class Mail {
    protected $to = null;
    protected $subject = null;
    protected $message = null;
    protected $additionalHeaders = null;
    protected $additionalParameters = null;

    /**
     * Set recipient of the E-Mail
     * 
     * @param string $value The recipients E-Mail address
     * @return Asatru\Mailwrapper\Mail
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
     * @return Asatru\Mailwrapper\Mail
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
     * @return Asatru\Mailwrapper\Mail
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
     * @return Asatru\Mailwrapper\Mail
     */
    public function setView($layout, array $yields, array $data = [])
    {
        $this->message = view($layout, $yields, $data)->out(true);

        return $this;
    }

    /**
     * Set additional headers for the E-Mail
     * 
     * @param mixed $value The additional headers
     * @return Asatru\Mailwrapper\Mail
     */
    public function setAdditionalHeaders($value)
    {
        $this->additionalHeaders = $value;

        return $this;
    }

    /**
     * Set additional parameters for the E-Mail
     * 
     * @param string $value The additional parameters
     * @return Asatru\Mailwrapper\Mail
     */
    public function setAdditionalParameters($value)
    {
        $this->additionalParameters = $value;

        return $this;
    }

    /**
     * Send the actual E-Mail
     * 
     * @return boolean Returns the result of the mail() call
     */
    public function send()
    {
        return mail($this->to, $this->subject, wordwrap($this->message, 70), 'Content-type: text/html; charset=utf-8\r\n' . $this->additionalHeaders, $this->additionalParameters);
    }
}