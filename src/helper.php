<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

namespace Asatru\Helper;

//Actual flash message item
class FlashMsgItem {
    private $name = null;
    private $msg = null;

    public function __construct($name, $msg)
    {
        //Construct object

        $this->name = $name;
        $this->msg = $msg;
    }

    public function getName()
    {
        //Get name

        return $this->name;
    }

    public function getMsg()
    {
        //Get message

        return $this->msg;
    }
}

//This components handles flash messages
class FlashMessage {
    public static function setMsg($name, $msg)
    {
        //Set new flash message

        $_SESSION[$name] = new FlashMsgItem($name, $msg);
    }

    public static function hasMsg($name)
    {
        //Check if flash message exists

        return (isset($_SESSION[$name]) && ($_SESSION[$name]) instanceof FlashMsgItem);
    }

    public static function getMsg($name)
    {
        //Get flash message

        if ((!isset($_SESSION[$name])) || (!($_SESSION[$name] instanceof FlashMsgItem))) {
            return false;
        }

        return $_SESSION[$name]->getMsg();
    }

    public static function clearAll()
    {
        //Clear all flash messages

        foreach ($_SESSION as $key => $item) {
            if ($item instanceof FlashMsgItem) {
                unset($_SESSION[$key]);
            }
        }
    }
}

function base_path()
{
    //Return root path of project

    return str_replace('\\', '/', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
}

function app_path()
{
    //Return path to app directory

    return base_path() . '/app';
}

function resource_path()
{
    //Return path to resource directory

    return base_path() . '/app/resources';
}

function base_url()
{
    //Return the base URL

    return (((isset($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] != 'off')) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . (($_SERVER['SERVER_PORT'] != 80) ? ':' . $_SERVER['SERVER_PORT'] : '') . $_ENV['APP_BASEDIR'];
}

function app_url()
{
    //Return URL to app directory

    return base_url() . '/app';
}

function resource_url()
{
    //Return URL to resources directory

    return base_url() . '/app/resources';
}

function csrf_token()
{
    //Return the CSRF token

    return $_SESSION['csrf_token'];
}