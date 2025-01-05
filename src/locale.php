<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2025 by Daniel Brendel
    
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace Asatru\Lang {
    /**
     * This component handles the locale
     */
    class Language {
        private $lang = [];

        /**
         * Load language file if requested
         * 
         * @param string $lang The locale identifier
         * @return void
         */
        public function __construct($lang = null)
        {
            if ($lang) {
                $this->load($lang);
            }
        }

        /**
         * Load language files according to locale
         * 
         * @param string $locale The locale identifier
         * @return void
         */
        public function load($locale)
        {
            $this->lang = [];

            if (!is_dir(ASATRU_APP_ROOT . '/app/lang/' . $locale)) {
                throw new \Exception('Language \'' . $locale . '\' not found.');
            }

            $files = scandir(ASATRU_APP_ROOT . '/app/lang/' . $locale); //Get all files of directory
            foreach($files as $file) {
                if (!is_dir(ASATRU_APP_ROOT . '/app/lang/' . $locale . '/' . $file)) { //If it's not a directory
                    if (pathinfo(ASATRU_APP_ROOT . '/app/lang/' . $locale . '/' . $file, PATHINFO_EXTENSION) === 'php') { //If it's a PHP script
                        $item = array('file' => pathinfo($file, PATHINFO_FILENAME), 'phrases' => require(ASATRU_APP_ROOT . '/app/lang/' . $locale . '/' . $file)); //Create item with the phrases
                        array_push($this->lang, $item); //Add to array
                    }
                }
            }
        }

        /**
         * Query phrase from array
         * 
         * @param string $qry The query string containing the file and the phrase delimited by a dot
         * @param array $params optional A key-value pair with variables for the phrase
         * @return string The localization phrase or the qry if not found
         */
        public function query($qry, $params = null)
        {
            if (strpos($qry, '.') !== false) {
                $spl = explode('.', $qry); //First token is the language file and the second token the phrase
                if (count($spl) == 2) {
                    foreach ($this->lang as $item) { //Loop through language files
                        if ($item['file'] == $spl[0]) { //Check for the name
                            foreach ((array)$item['phrases'] as $ident => $phrase) { //If matched then loop through the phrases
                                if ($ident == $spl[1]) { //Check for the requested phrase
                                    $result = $phrase;

                                    //Replace variables if any
                                    if (is_array($params)) {
                                        foreach ($params as $key => $value) {
                                            if (strpos($result, '{' . $key . '}') !== false) {
                                                $result = str_replace('{' . $key . '}', $value, $result);
                                            }
                                        }
                                    }

                                    return $result;
                                }
                            }
                        }
                    }
                }
            }

            return $qry;
        }
    }

    /**
     * This components handles the locale
     */
    class Locale {
        /**
         * Instantiate object
         * 
         * @return void
         */
        public function __construct()
        {
            $this->createCookieIfNotExists();
        }

        /**
         * Create the cookie for locale if not already exists
         * 
         * @return void
         */
        public function createCookieIfNotExists()
        {
            if (!isset($_COOKIE['COOKIE_LOCALE'])) {
                $this->setLocale('en'); //Defaulted to english
            }
        }

        /**
         * Set new locale cookie value
         * 
         * @param string $lang The locale identifier
         * @return void
         */
        public function setLocale($lang)
        {
            setcookie('COOKIE_LOCALE', $lang, time() + 60 * 60 * 24 * 365, '/');
        }

        /**
         * Get current locale
         * 
         * @return string The current locale identifier from the cookie
         */
        public function getLocale()
        {
            return isset($_COOKIE['COOKIE_LOCALE']) ? $_COOKIE['COOKIE_LOCALE'] : 'en';
        }
    }
}

namespace {
    //Instantiate locale handler and create cookie if not exists
    $objLocale = new Asatru\Lang\Locale();
    $objLocale->createCookieIfNotExists();

    //Instantiate language handler and load language
    $objLanguage = new Asatru\Lang\Language();
    if (isset($_COOKIE['COOKIE_LOCALE'])) {
        $objLanguage->load($_COOKIE['COOKIE_LOCALE']);
    } else {
        $objLanguage->load('en');
    }

    if (!function_exists('__')) {
        /**
         * Query phrase from array
         * 
         * @param string $qry The query string containing the file and the phrase delimited by a dot
         * @param array $params optional A key-value pair with variables for the phrase
         * @return string The localization phrase or the qry if not found
         */
        function __($phrase, $params = null)
        {
            global $objLanguage;
            return $objLanguage->query($phrase, $params);
        }
    }

    if (!function_exists('setLanguage')) {
        /**
         * Set new locale and load language content
         * 
         * @param string $lang The locale identifier
         * @return void
         */
        function setLanguage($lang)
        {
            global $objLocale;
            global $objLanguage;
            
            $objLocale->setLocale($lang);
            $objLanguage->load($lang);
        }
    }

    if (!function_exists('getLocale')) {
        /**
         * Get current locale
         * 
         * @return string The current locale identifier from the cookie
         */
        function getLocale()
        {
            global $objLocale;
            return $objLocale->getLocale();
        }
    }
}