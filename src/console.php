<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2023 by Daniel Brendel
    
    Version: 1.0
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace Asatru\Console;

/**
 * Create a model with the associated migration
 * 
 * @param string $name The name of the model and migration
 * @param string $table The name of the database table
 * @return boolean
 */
function createModel($name, $table)
{
    $content1 = "<?php

    /*
        Asatru PHP - Migration for " . $table . "
    */

    /**
     * This class specifies a migration
     */
    class " . ucfirst($name) . "_Migration {
        private \$database = null;
        private \$connection = null;

        /**
         * Store the PDO connection handle
         * 
         * @param \\PDO \$pdo The PDO connection handle
         * @return void
         */
        public function __construct(\$pdo)
        {
            \$this->connection = \$pdo;
        }

        /**
         * Called when the table shall be created or modified
         * 
         * @return void
         */
        public function up()
        {
            \$this->database = new Asatru\Database\Migration('" . $table . "', \$this->connection);
            \$this->database->drop();
            \$this->database->add('id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
            \$this->database->add('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
            \$this->database->create();
        }

        /**
         * Called when the table shall be dropped
         * 
         * @return void
         */
        public function down()
        {
            if (\$this->database)
                \$this->database->drop();
        }
    }";

    if (!file_put_contents(ASATRU_APP_ROOT . '/app/migrations/' . $name . '.php', $content1)) {
        return false;
    }

    $content2 = "<?php

    /*
        Asatru PHP - Model for ". $table . "
    */

    /**
     * This class extends the base model class and represents your associated table
     */ 
    class " . $name . " extends \Asatru\Database\Model {
        /**
         * Return the associated table name of the migration
         * 
         * @return string
         */
        public static function tableName()
        {
            return '" . $table . "';
        }
    }";

    if (!file_put_contents(ASATRU_APP_ROOT . '/app/models/' . $name . '.php', $content2)) {
        return false;
    }

    return true;
}

/**
 * Create a module
 * 
 * @param string $name The name of the module
 * @return boolean
 */
function createModule($name)
{
    $content = "<?php

    /*
        Asatru PHP - Module
    */

    /**
     * This class represents your module
     */
    class " . ucfirst($name) . " {
        public function __construct()
        {
            //
        }

        public function __destruct()
        {
            //
        }
    }
";

    return file_put_contents(ASATRU_APP_ROOT . '/app/modules/' . $name . '.php', $content) !== false;
}

/**
 * Create a controller
 * 
 * @param string $name The name of the controller
 * @return boolean
 */
function createController($name)
{
    $content = "<?php

    /*
        Asatru PHP - Controller
    */

    /**
     * This class represents your controller
     */
    class " . ucfirst($name) . "Controller {
        //
    }
";

    return file_put_contents(ASATRU_APP_ROOT . '/app/controller/' . $name . '.php', $content) !== false;
}

/**
 * Create language structure for ident
 * 
 * @param string $ident The identifier of the locale
 */
function createLang($ident)
{
    if (is_dir(ASATRU_APP_ROOT . '/app/lang/' . $ident)) {
        return false;
    }

    mkdir(ASATRU_APP_ROOT . '/app/lang/' . $ident);

    $content = "<?php

    /*
        Asatru PHP - Language file for " . $ident . "
    */

    return [
        //
    ];";
    
    if (!file_put_contents(ASATRU_APP_ROOT . '/app/lang/' . $ident . '/app.php', $content)) {
        return false;
    }

    return true;
}

/**
 * Create a new custom validator
 * 
 * @param string $name The name of the validator
 * @param string $ident The identifier to be used in the validation call
 * @return boolean
 */
function createValidator($name, $ident)
{
    if (!is_dir(ASATRU_APP_ROOT . '/app/validators')) {
        mkdir(ASATRU_APP_ROOT . '/app/validators');
    }

    $content = "<?php

        /*
            Asatru PHP - Validator for validation ident " . $ident . "
        */

        /**
         * This class implements your validator
         */
        class " . ucfirst($name) . "Validator extends Asatru\Controller\BaseValidator {
            protected \$error;

            /**
             * Return the identifier of the validator
             * 
             * @return string
             */
            public function getIdent()
            {
                return '" . $ident . "';
            }

            /**
             * Validate the actual input data
             * 
             * @param mixed \$value The input value
             * @param mixed \$args optional Arguments for the validator
             * @return boolean True if valid, otherwise false
             */
            public function verify(\$value, \$args = null)
            {
                return true;
            }

            /**
             * Return error description of the validation process if the data is invalid
             * 
             * @return string
             */
            public function getError()
            {
                return \$this->error;
            }
        }
    ";

    if (!file_put_contents(ASATRU_APP_ROOT . '/app/validators/' . strtolower($name) . '.php', $content)) {
        return false;
    }

    return true;
}

/**
 * Create a new event handler
 * 
 * @param string $name The name of the event handler
 * @param string $initial_handler An initial handler to be provided
 * @return boolean
 */
function createEvent($name, $initial_handler)
{
    if (!is_dir(ASATRU_APP_ROOT . '/app/events')) {
        mkdir(ASATRU_APP_ROOT . '/app/events');
    }

    $content = "<?php

		/*
			Asatru PHP - Event handler
		*/

		/**
		 * Event handler class
		 */
		class $name {
			/**
			 * An initial event handler method
			 * 
			 * @param \$data optional
			 * @return void
			 */
			public function $initial_handler(\$data = null)
			{
			}
		}
    ";

    if (!file_put_contents(ASATRU_APP_ROOT . '/app/events/' . strtolower($name) . '.php', $content)) {
        return false;
    }

    return true;
}

/**
 * Create authentication model and migration
 * 
 * @return boolean
 */
function createAuth()
{
    $content1 = "<?php
    /*
        Asatru PHP - Migration for Auth
    */

    /**
     * Authentication migration
     */
    class Auth_Migration {
        private \$database = null;
        private \$connection = null;

        /**
         * Set PDO connection handle
         * 
         * @param \\PDO \$pdo The PDO connection handle
         * @return void
         */
        public function __construct(\$pdo)
        {
            \$this->connection = \$pdo;
        }

        /**
         * Create the authentication database table
         * 
         * @return void
         */
        public function up()
        {
            \$this->database = new Asatru\Database\Migration('Auth', \$this->connection);
            \$this->database->drop();
            \$this->database->add('id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
            \$this->database->add('email VARCHAR(255) NOT NULL');
            \$this->database->add('username VARCHAR(255) NOT NULL');
            \$this->database->add('password VARCHAR(255) NOT NULL');
            \$this->database->add('session VARCHAR(255) NOT NULL');
            \$this->database->add('status INT(1) NOT NULL DEFAULT 0');
            \$this->database->add('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
            \$this->database->create();
        }

        /**
         * Drop the authentication database table
         * 
         * @return void
         */
        public function down()
        {
            if (\$this->database)
                \$this->database->drop();
        }
    }
    ";

    $content2 = "<?php
    /*
        Asatru PHP - Model for Auth

        Default authentication model
    */

    /**
     * Model representing the authentication table
     */
    class Auth extends \Asatru\Database\Model {
        /**
         * Add a new user registration
         * 
         * @param string \$username The name of the user
         * @param string \$email The user E-Mail address
         * @param string \$password The password given by the user
         * @return boolean
         */
        public static function register(string \$username, string \$email, string \$password)
        {
            if ((\$username === '') || (\$password === '') || (filter_var(\$email, FILTER_VALIDATE_EMAIL) === false))
                return false;

            \$byemail = Auth::getByEmail(\$email);
            if (\$byemail->count() > 0)
                return false;

            try {
                Auth::insert('username', \$username)->insert('email', \$email)->insert('password', password_hash(\$password, PASSWORD_DEFAULT))->go();
            } catch (\Exception \$e) {
                return false;
            }

            return true;
        }

        /**
         * Log the user in
         * 
         * @param string \$email The E-Mail address of the user
         * @param string \$password The user password
         * @return boolean
         */
        public static function login(string \$email, string \$password)
        {
            \$byemail = Auth::getByEmail(\$email);
            if (\$byemail->count() === 0)
                return false;

            if (!password_verify(\$password, \$byemail->get(0)->get('password')))
                return false;
            
            try {
                Auth::update('status', 1)->update('session', session_id())->where('email', '=', \$email)->go();
            } catch (\Exception \$e) {
                return false;
            }

            return true;
        }

        /**
         * Log the user out
         * 
         * @param string \$email The user E-Mail address
         * @return boolean
         */
        public static function logout(string \$email)
        {
            \$byemail = Auth::getByEmail(\$email);
            if (\$byemail->count() === 0)
                return false;

            try {
                Auth::update('status', 0)->update('session', '')->where('email', '=', \$email)->go();
            } catch (\Exception \$e) {
                return false;
            }

            return true;
        }

        /**
         * Check if a user is currently logged in (either by E-Mail address or by session)
         * 
         * @param string|null \$email The user E-Mail address
         * @return boolean
         */
        public static function isUserLoggedIn(\$email = null)
        {
            \$userdata = null;

            if (\$email === null) {
                \$userdata = Auth::getBySession();
            } else {
                \$userdata = Auth::getByEmail(\$email);
            }

            if (\$userdata->count() === 0)
                return false;
            
            return \$userdata->get(0)->get('status') === '1';
        }

        /**
         * Get user by email
         * 
         * @param string \$email The users E-Mail address
         * @return Asatru\Database\Collection|boolean User data collection on success, otherwise false
         */
        public static function getByEmail(string \$email)
        {
            try {
                \$result = Auth::where('email', '=', \$email)->first();
            } catch (\Exception \$e) {
                return false;
            }

            return \$result;
        }

        /**
         * Get user by session
         * 
         * @return Asatru\Database\Collection|boolean User data collection on success, otherwise false
         */
        public static function getBySession()
        {
            try {
                \$result = Auth::where('session', '=', session_id())->first();
            } catch (\Exception \$e) {
                return false;
            }

            return \$result;
        }

        /**
         * Return the associated table name of the migration
         * 
         * @return string
         */
        public static function tableName()
        {
            return 'Auth';
        }
    }
    ";

    if (!file_put_contents(ASATRU_APP_ROOT . '/app/migrations/Auth.php', $content1)) {
        return false;
    }

    if (!file_put_contents(ASATRU_APP_ROOT . '/app/models/Auth.php', $content2)) {
        return false;
    }

    return true;
}

/**
 * Create caching model and migration
 * 
 * @return boolean
 */
function createCache()
{
    $content1 = "<?php

	/*
		Asatru PHP - Migration vor Caching
	*/

	class Cache_Migration {
		private \$database = null;
		private \$connection = null;

		/**
		 * Construct class and store PDO connection handle
		 * 
		 * @param \PDO \$pdo
		 * @return void
		 */
		public function __construct(\$pdo)
		{
			\$this->connection = \$pdo;
		}

		/**
		 * Called when the table shall be created or modified
		 * 
		 * @return void
		 */
		public function up()
		{
			\$this->database = new Asatru\Database\Migration('Cache', \$this->connection);
			\$this->database->drop();
			\$this->database->add('id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
			\$this->database->add('ident VARCHAR(260) NOT NULL');
			\$this->database->add('value BLOB NULL');
			\$this->database->add('updated_at TIMESTAMP');
			\$this->database->add('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
			\$this->database->create();
		}

		/**
		 * Called when the table shall be dropped
		 * 
		 * @return void
		 */
		public function down()
		{
			\$this->database = new Asatru\Database\Migration('Cache', \$this->connection);
			\$this->database->drop();
		}
	}";

    $content2 = "<?php
	/*
		Asatru PHP - Model for Caching
	*/

	class Cache extends \Asatru\Database\Model {
		/**
		 * Obtain value either from cache or from closure
		 *	
		 *	@param string \$ident The cache item identifier
		 *	@param int \$timeInSeconds Amount of seconds the item shall be cached
		 *	@param \$closure Function to be called for the actual value
		 *	@return mixed
		 */
		public static function remember(\$ident, \$timeInSeconds, \$closure)
		{
			\$item = Cache::find(\$ident, 'ident');
			if (\$item->count() == 0) {
				\$value = \$closure();
				
				\$data = array(
					'ident' => \$ident,
					'value' => \$value,
					'updated_at' => date('Y-m-d H:i:s')
				);
				
				foreach (\$data as \$key => \$val) {
					Cache::insert(\$key, \$val);
				}
				
				Cache::go();
				
				return \$value;
			} else {
				\$data = \$item->get(0);
				\$dtLast = new DateTime(date('Y-m-d H:i:s', strtotime(\$data->get('updated_at'))));
				\$dtLast->add(new DateInterval('PT' . \$timeInSeconds . 'S'));
				\$dtNow = new DateTime('now');

				if (\$dtNow < \$dtLast) {
					return \$data->get('value');
				} else {
					\$value = \$closure();
					
					\$updData = array(
						'value' => \$value,
						'updated_at' => date('Y-m-d H:i:s')
					);
					
					foreach (\$updData as \$key => \$val) {
						Cache::update(\$key, \$val);
					}
					
					Cache::go();
					
					return \$value;
				}
			}
			
			return null;
		}
		
		/**
		 * Check for item existence
		 *
		 *	@param \$ident
		 *  @return bool
		 */
		public static function has(\$ident)
		{
			\$item = Cache::find(\$ident, 'ident');
			if (\$item->count() > 0) {
				return true;
			}
			
			return false;
		}
		
		/**
		 * Get item and then delete it
		 *
		 *	@param \$ident
		 *  @return mixed
		 */
		public static function pull(\$ident)
		{
			\$item = Cache::find(\$ident, 'ident');
			if (\$item->count() > 0) {
				\$data = \$item->get(0);
				
				Cache::where('id', '=', \$item->get(0)->get('id'))->delete();
				
				return \$data->get('value');
			}
			
			return null;
		}
		
		/**
		 * Forget cache item
		 * 
		 * @param string \$ident The item identifier
		 * @return bool
		 */
		public static function forget(\$ident)
		{
			\$item = Cache::find(\$ident, 'ident');
			if (\$item->count() > 0) {
				Cache::where('id', '=', \$item->get(0)->get('id'))->delete();
				
				return true;
			}
			
			return false;
		}
		
		/**
		 * Return the associated table name of the migration
		 * 
		 * @return string
		 */
		public static function tableName()
		{
			return 'Cache';
		}
	}
    ";

    if (!file_put_contents(ASATRU_APP_ROOT . '/app/migrations/Cache.php', $content1)) {
        return false;
    }

    if (!file_put_contents(ASATRU_APP_ROOT . '/app/models/Cache.php', $content2)) {
        return false;
    }

    return true;
}

/**
 * Create a new test
 * 
 * @param string $name The name of the test case
 * @return boolean
 */
function createTest($name)
{
    if (!is_dir(ASATRU_APP_ROOT . '/app/tests')) {
        mkdir(ASATRU_APP_ROOT . '/app/tests');

        $bootstrap = "
        <?php

        /*
            Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
            
            (C) 2019 - 2023 by Daniel Brendel
            
            Version: 1.0
            Contact: dbrendel1988<at>gmail<dot>com
            GitHub: https://github.com/danielbrendel/
            
            Released under the MIT license
        */
        
        //If composer is installed we utilize its autoloader
        if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
            require_once __DIR__ . '/../../vendor/autoload.php';
        }
		
		//Set application root directory path
		define('ASATRU_APP_ROOT', __DIR__ . '/../..');
        
        //Fetch constants
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/constants.php';
        
        //Require the controller component
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/controller.php';
        
        //Require logging
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/logger.php';
        
        //Require .env config management
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/dotenv.php';
        
        //Require autoload component
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/autoload.php';

        //Require config component
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/config.php';
        
        //Require helpers
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/helper.php';

        //Require Html helper
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/html.php';

        //Require form helper
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/forms.php';
		
		//Require mail wrapper
		require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/mailwrapper.php';
        
        //Require testing component
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/testing.php';
        
        //Parse .env file if it exists
        if (file_exists(__DIR__ . '/../../.env.testing')) {
            env_parse(__DIR__ . '/../../.env.testing');
        }
        
        //Enable debug mode error handling
        \$_ENV['APP_DEBUG'] = true;
        error_reporting(E_ALL);
        
        //Check if we shall create/continue a session
        if ((isset(\$_ENV['APP_SESSION'])) && (\$_ENV['APP_SESSION'])) {
            if (!session_start()) {
                throw new Exception('Failed to create/continue the session');
            }
        
            if (!isset(\$_SESSION['continued'])) { //Check if a new session
                //Create CSRF-token
                \$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
                //Mark session
                \$_SESSION['continued'] = true;
            }
        }
        
        //Require localization
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/locale.php';
        
        //Require database management
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/database.php';
        
        //Require event manager
        require_once __DIR__ . '/../../vendor/danielbrendel/asatru-php-framework/src/events.php';
        
        //Perform autoloading
        \$auto = new Asatru\Autoload\Autoloader(__DIR__ . '/../config/autoload.php');
        \$auto->load();
        
        //Load validators if any
        Asatru\Controller\CustomPostValidators::load(__DIR__ . '/../validators');
        ";

        if (!file_put_contents(ASATRU_APP_ROOT . '/app/tests/bootstrap.php', $bootstrap)) {
            return false;
        }
    }

    $content = "<?php

    /*
        Testcase for Test " . ucfirst($name) .
    "*/

    use PHPUnit\Framework\TestCase;
    
    /**
     * This class holds your test methods
     */
    class " . ucfirst($name) . "Test extends Asatru\Testing\Test
    {
        //
    }
    ";

    if (!file_put_contents(ASATRU_APP_ROOT . '/app/tests/' . ucfirst($name) . 'Test.php', $content)) {
        return false;
    }

    return true;
}

/**
 * Validate if database feature is enabled
 * 
 * @return void
 */
function validate_database_enabled()
{
    if ((!isset($_ENV['DB_ENABLE'])) || (!$_ENV['DB_ENABLE'])) {
        echo "\033[31mDatabase feature is disabled in environment config\033[39m\n";
        exit(1);
    }
}

/**
 * Process the input of the console
 * 
 * @param array $argv The arguments of the execution
 * @return void
 */
function handleInput($argv)
{
    //Handle console input

    if ((!isset($argv[1])) || ($argv[1] === 'help')) {
        echo "\033[33m" . ASATRU_FW_NAME . " v" . ASATRU_FW_VERSION . " by " . ASATRU_FW_AUTHOR . " (" . ASATRU_FW_CONTACT . ") - CLI interface\n\n\033[39m\n";
        echo "The following commands are available:\n";
        echo "+ help: Displays this help text\n";
        echo "+ make:model <name> <table>: Creates a new model with migration\n";
        echo "+ make:module <name>: Creates a new module for business logics\n";
        echo "+ make:controller <name>: Creates a new controller\n";
        echo "+ make:language <ident>: Creates a new language folder with app.php\n"; 
        echo "+ make:validator <name> <ident>: Creates a new validator\n";
        echo "+ make:event <name> <handler> Creates a new event handler\n";
        echo "+ make:auth: Creates new authentication model and migration\n";
        echo "+ make:test <name>: Creates a new test case\n";
        echo "+ migrate:fresh: Drops all migrations and creates all new\n";
        echo "+ migrate:list: Creates only all new created migrations\n";
        echo "+ migrate:drop: Drops all migrations\n";
        echo "+ serve <port>: Spawns a development server. If port is not provided it uses port " . DEVSERV_DEFAULT_PORT . "\n";
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
    } else if ($argv[1] === 'make:module') {
        if (!isset($argv[2])) {
            echo "\033[31mYou must specify the module name\033[39m\n";
            exit(0);
        }

        if (!createModule($argv[2])) {
            echo "\033[31mFailed to create module\033[39m\n";
        } else {
            echo "\033[32mModule has been created!\033[39m\n";
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
            echo "\033[31mYou must specify the name of the validator and also its identifier\033[39m\n";
            exit(0);
        }

        if (!createValidator($argv[2], $argv[3])) {
            echo "\033[31mFailed to create the validator\033[39m\n";
        } else {
            echo "\033[32mThe validator has been created!\033[39m\n";
        }
	} else if ($argv[1] === 'make:event') {
        if ((!isset($argv[2])) || (!isset($argv[3]))) {
            echo "\033[31mYou must specify the name of the event handler and also its initial handler method\033[39m\n";
            exit(0);
        }

        if (!createEvent($argv[2], $argv[3])) {
            echo "\033[31mFailed to create the event handler\033[39m\n";
        } else {
            echo "\033[32mThe event handler has been created!\033[39m\n";
		}
    } else if ($argv[1] === 'make:auth') {
        if (!createAuth()) {
            echo "\033[31mFailed to create auth objects\033[39m\n";
        } else {
            echo "\033[32mThe auth objects have been created!\033[39m\n";
        }
	} else if ($argv[1] === 'make:cache') {
        if (!createCache()) {
            echo "\033[31mFailed to create cache objects\033[39m\n";
        } else {
            echo "\033[32mThe cache objects have been created!\033[39m\n";
        }
    } else if ($argv[1] === 'make:test') {
        if (!createTest($argv[2])) {
            echo "\033[31mFailed to create test case\033[39m\n";
        } else {
            echo "\033[32mThe test case has been created!\033[39m\n";
        }
    } else if ($argv[1] === 'migrate:fresh') {
        validate_database_enabled();
        migrate_fresh();

        echo "\033[32mThe database has been freshly migrated!\033[39m\n";
    } else if ($argv[1] === 'migrate:list') {
        validate_database_enabled();
        migrate_list();

        echo "\033[32mThe database has been listly migrated!\033[39m\n";
    } else if ($argv[1] === 'migrate:drop') {
        validate_database_enabled();
        migrate_drop();

        echo "\033[32mThe database has been cleared!\033[39m\n";
    } else if ($argv[1] === 'serve') {
        $retval = 0;
        $port = isset($argv[2]) ? $argv[2] : DEVSERV_DEFAULT_PORT;
        echo "\033[32mLocal development server started at localhost:" . $port . "\033[39m\n";
        system('php -S localhost:' . $port . ' -t public/', $retval);
    } else {
        echo "\033[31mCommand " . $argv[1] . " is unknown\033[39m\n";
    }
}