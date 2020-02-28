<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace {
    //Include all modules
    $modules = scandir(__DIR__ . '/../../../../app/modules');
    if ($modules !== false) {
        foreach ($modules as $file) {
            if (pathinfo(__DIR__ . '/../../../../app/modules/' . $file, PATHINFO_EXTENSION) == 'php') {
                require_once __DIR__ . '/../../../../app/modules/' . $file;
                //Check if class name equals the file name
                $className = pathinfo($file, PATHINFO_FILENAME);
                try {
                    $obj = new $className();
                } catch (\Exception $e) {
                    throw new \Exception("Script {$className}.php does not have an associated class");
                }
            }
        }
    }
}