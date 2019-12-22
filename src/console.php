<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

namespace Asatru\Console;

function createModel($name, $table)
{
    //Create a model with the associated migration

    $content1 = "<?php

    /*
        Asatru PHP - Migration for " . $table . "
    */

    class " . ucfirst($name) . "_Migration {
        private \$database = null;
        private \$connection = null;

        public function __construct(\$pdo)
        {
            \$this->connection = \$pdo;
        }

        public function up()
        {
            \$this->database = new Asatru\Database\Migration('" . $table . "', \$this->connection);
            \$this->database->drop();
            \$this->database->add('id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
            \$this->database->add('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
            \$this->database->create();
        }

        public function down()
        {
            if (\$this->database)
                \$this->database->drop();
        }
    }";

    if (!file_put_contents(__DIR__ . '/../../../../app/migrations/' . $name . '_migration.php', $content1)) {
        return false;
    }

    $content2 = "<?php

    /*
        Asatru PHP - Model for ". $table . "
    */

    use Asatru\Database;

    class " . $name . " extends \Asatru\Database\Model {
        public static function tableName()
        {
            //Return the associated table name of the migration

            return '" . $table . "';
        }
    }";

    if (!file_put_contents(__DIR__ . '/../../../../app/models/' . $name . '.php', $content2)) {
        return false;
    }

    return true;
}

function createController($name)
{
    //Create a controller

    $content = "<?php

    /*
        Asatru PHP - Controller
    */

    use Asatru\View;
    use Asatru\Helper;

    class " . ucfirst($name) . "Controller {
        //
    }
";

    return file_put_contents(__DIR__ . '/../../../../app/controller/' . $name . '.php', $content) !== false;
}

function createLang($ident)
{
    //Create language structure for ident

    if (is_dir(__DIR__ . '/../../../../app/lang/' . $ident)) {
        return false;
    }

    mkdir(__DIR__ . '/../../../../app/lang/' . $ident);

    $content = "<?php

    /*
        Asatru PHP - Language file for " . $ident . "
    */

    return [
        //
    ];";
    
    if (!file_put_contents(__DIR__ . '/../../../../app/lang/' . $ident . '/app.php', $content)) {
        return false;
    }

    return true;
}

function createValidator($name, $ident)
{
    if (!is_dir(__DIR__ . '/../../../../app/validators')) {
        mkdir(__DIR__ . '/../../../../app/validators');
    }

    $content = "<?php

        /*
            Asatru PHP - Validator for validation ident " . $ident . "
        */

        class " . ucfirst($name) . "Validator extends Asatru\Controller\BaseValidator {
            protected \$error;

            public function getIdent()
            {
                return '" . $ident . "';
            }

            public function verify(\$value, \$args = null)
            {
                return true;
            }

            public function getError()
            {
                return \$this->error;
            }
        }
    ";

    if (!file_put_contents(__DIR__ . '/../../../../app/validators/' . strtolower($name) . '.php', $content)) {
        return false;
    }

    return true;
}

function handleInput($argv)
{
    //Handle console input

    if ((!isset($argv[1])) || ($argv[1] === 'help')) {
        echo "\033[33m" . ASATRU_FW_NAME . " v" . ASATRU_FW_VERSION . " by " . ASATRU_FW_AUTHOR . " (" . ASATRU_FW_CONTACT . ") - CLI interface\n\n\033[39m\n";
        echo "The following commands are available:\n";
        echo "+ help: Displays this help text\n";
        echo "+ make:model <name> <table>: Creates a new model with migration\n";
        echo "+ make:controller <name>: Creates a new controller\n";
        echo "+ make:language <ident>: Creates a new language folder with app.php\n"; 
        echo "+ make:validator <name> <ident>: Creates a new validator\n";
        echo "+ migrate:fresh: Drops all migrations and creates all new\n";
        echo "+ migrate:list: Creates only all new created migrations\n";
        echo "+ migrate:drop: Drops all migrations\n";
        echo "+ serve <port>: Spawns a development server. If port is not provided it uses port 8000\n";
    } else if ($argv[1] === 'make:model') {
        if (!isset($argv[2]) || (!isset($argv[3]))) {
            echo "\033[31mYou must specify the model name and the associated table name\033[39m\n";
            exit(0);
        }

        if (!createModel($argv[2], $argv[3])) {
            echo "\033[31mFailed to create model and migration\033[39m\n";
        } else {
            echo "\033[32mModel and migration files have been created!\033[39m\n";
        }
    } else if ($argv[1] === 'make:controller') {
        if (!isset($argv[2])) {
            echo "\033[31mYou must specify the controller name\033[39m\n";
            exit(0);
        }

        if (!createController($argv[2])) {
            echo "\033[31mFailed to create the controller\033[39m\n";
        } else {
            echo "\033[32mThe controller has been created!\033[39m\n";
        }
    } else if ($argv[1] === 'make:language') {
        if (!isset($argv[2])) {
            echo "\033[31mYou must specify the language identifier\033[39m\n";
            exit(0);
        }

        if (!createLang($argv[2])) {
            echo "\033[31mFailed to create the language content\033[39m\n";
        } else {
            echo "\033[32mThe language content has been created!\033[39m\n";
        }
    } else if ($argv[1] === 'make:validator') {
        if ((!isset($argv[2])) || (!isset($argv[3]))) {
            echo "\033[31mYou must the name of the validator and also its identifier\033[39m\n";
            exit(0);
        }

        if (!createValidator($argv[2], $argv[3])) {
            echo "\033[31mFailed to create the validator\033[39m\n";
        } else {
            echo "\033[32mThe validator has been created!\033[39m\n";
        }
    } else if ($argv[1] === 'migrate:fresh') {
        migrate_fresh();

        echo "\033[32mThe database has been freshly migrated!\033[39m\n";
    } else if ($argv[1] === 'migrate:list') {
        migrate_list();

        echo "\033[32mThe database has been listly migrated!\033[39m\n";
    } else if ($argv[1] === 'migrate:drop') {
        migrate_drop();

        echo "\033[32mThe database has been cleared!\033[39m\n";
    } else if ($argv[1] === 'serve') {
        $retval = 0;
        $port = isset($argv[2]) ? $argv[2] : 8000;
        echo "\033[32mLocal development server started at localhost:" . $port . "\033[39m\n";
        system('php -S localhost:' . $port, $retval);
    } else {
        echo "\033[31mCommand " . $argv[1] . " is unknown\033[39m\n";
    }
}