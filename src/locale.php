<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

namespace Asatru\Lang {
    //This component handles the locale
    class Language {
        private $lang = [];

        public function __construct($lang = null)
        {
            //Instantiate object

            //Load language file if requested
            if ($lang) {
                $this->load($lang);
            }
        }

        public function load($locale)
        {
            //Load language files according to locale
            
            $this->lang = [];

            $files = scandir(__DIR__ . '/../../../../app/lang/' . $locale);
            foreach($files as $file) {
                if (!is_dir(__DIR__ . '/../../../../app/lang/' . $locale . '/' . $file)) {
                    if (pathinfo(__DIR__ . '/../../../../app/lang/' . $locale . '/' . $file, PATHINFO_EXTENSION) === 'php') {
                        $item = array('file' => pathinfo($file, PATHINFO_FILENAME), 'phrases' => require_once(__DIR__ . '/../../../../app/lang/' . $locale . '/' . $file));
                        array_push($this->lang, $item);
                    }
                }
            }
        }

        public function query($qry)
        {
            //Query phrase from array
            
            if (strpos($qry, '.') !== false) {
                $spl = explode('.', $qry); //First token is the language file and the second token the phrase
                if (count($spl) == 2) {
                    foreach ($this->lang as $item) { //Loop through language files
                        if ($item['file'] == $spl[0]) { //Check for the name
                            foreach ($item['phrases'] as $ident => $phrase) { //If matched then loop through the phrases
                                if ($ident == $spl[1]) { //Check for the requested phrase
                                    return $phrase;
                                }
                            }
                        }
                    }
                }
            }

            return $qry;
        }
    }

    //This components handles the locale
    class Locale {
        public function __construct()
        {
            //Instantiate object

            $this->createCookieIfNotExists();
        }

        public function createCookieIfNotExists()
        {
            //Create the cookie for locale if not already exists

            if (!isset($_COOKIE[COOKIE_LOCALE])) {
                $this->setLocale('en'); //Defaulted to english
            }
        }

        public function setLocale($lang)
        {
            //Set new locale

            setcookie(COOKIE_LOCALE, $lang, time() + 60 * 60 * 24 * 365, '/');
        }

        public function getLocale()
        {
            //Get current locale

            return isset($_COOKIE[COOKIE_LOCALE]) ? $_COOKIE[COOKIE_LOCALE] : 'en';
        }
    }
}

namespace {
    //Instantiate locale handler and create cookie if not exists
    $objLocale = new Asatru\Lang\Locale();
    $objLocale->createCookieIfNotExists();

    //Instantiate language handler and load language
    $objLanguage = new Asatru\Lang\Language();
    if (isset($_COOKIE[COOKIE_LOCALE])) {
        $objLanguage->load($_COOKIE[COOKIE_LOCALE]);
    } else {
        $objLanguage->load('en');
    }

    //Create the shortcut language query function
    if (!function_exists('__')) {
        function __($phrase)
        {
            global $objLanguage;
            return $objLanguage->query($phrase);
        }
    }

    //Create the language chooser function
    if (!function_exists('setLanguage')) {
        function setLanguage($lang)
        {
            global $objLocale;
            global $objLanguage;
            
            $objLocale->setLocale($lang);
            $objLanguage->load($lang);
        }
    }

    //Create the locale getter function
    if (!function_exists('getLocale')) {
        function getLocale()
        {
            global $objLocale;
            return $objLocale->getLocale();
        }
    }
}