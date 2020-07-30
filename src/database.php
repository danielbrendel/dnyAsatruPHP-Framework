<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>gmail<dot>com
    GitHub: https://github.com/danielbrendel/
    
    Released under the MIT license
*/

namespace Asatru\Database {
    /**
     * This component handles the table creation
     */
    class Migration {
        private $handle = null;
        private $command = null;
        private $name = null;

        /**
         * Initialize table creation
         * 
         * @param string $name The name of the migration
         * @param \PDO $con PDO handler instance  
         * @throws \Exception
         */
        public function __construct($name, $con)
        {
            if ((!$name) || (strlen($name) === 0)) {
                throw new \Exception('Migration name must be provided');
            }

            $this->handle = $con;
            
            if ($_ENV['DB_DRIVER'] === 'mysql') {
                $this->handle->exec('USE ' . $_ENV['DB_DATABASE'] . ';');
            }

            $error = $this->handle->errorInfo();
            if ($error[0] !== '00000') {
                throw new \Exception('SQL error: ' . $error[0] . ':' . $error[1] . ' -> ' . $error[2]);
            }

            $this->command = 'CREATE TABLE ' . $name . ' (';

            $this->name = $name;
        }

        /**
         * Add new column
         * 
         * @param string $column The column definition
         * @return void
         */
        public function add($column)
        {
            $this->command .= (($this->command[strlen($this->command)-1] !== '(') ? ', ' : '') . $column;
        }

        /**
         * Append new column
         * 
         * @param string $column The column definition to be appended
         * @return void
         */
        public function append($column)
        {
            $this->handle->exec('ALTER TABLE ' . $this->name . ' ADD ' . $column . ';');
        }

        /**
         * Create table
         * 
         * @return void
         * @throws \Exception
         */
        public function create()
        {
            $this->command .= ');';

            $this->handle->exec($this->command);

            $error = $this->handle->errorInfo();
            if ($error[0] !== '00000') {
                throw new \Exception('SQL error: ' . $error[0] . ':' . $error[1] . ' -> ' . $error[2]);
            }
        }

        /**
         * Drop the table
         * 
         * @return void
         * @throws \Exception
         */
        public function drop()
        {
            $this->handle->exec('DROP TABLE IF EXISTS ' . $this->name . ';');

            $error = $this->handle->errorInfo();
            if ($error[0] !== '00000') {
                throw new \Exception('SQL error: ' . $error[0] . ':' . $error[1] . ' -> ' . $error[2]);
            }
        }

        /**
         * Get table name
         * 
         * @return string The name of the table
         */
        public function getTableName()
        {
            return $this->name;
        }
    }

    /**
     * This component represents a database query result collection
     */
    class Collection {
        private $items = array();

        /**
         * Create object from array
         * 
         * @param array $arr The target array
         * @eturn void
         */
        public function __construct($arr)
        {
            $this->createFromArray($arr);
        }

        /**
         * Create collection from array
         * 
         * @param array The array to be converted to \Collection
         * @return void
         */
        private function createFromArray($arr)
        {
            foreach ($arr as $key => $value) {
                if (is_array($value)) {
                    $this->items[$key] = new Collection($value);
                } else {
                    $this->items[$key] = $value;
                }
            }
        }

        /**
         * Return amount of items
         * 
         * @return int
         */
        public function count()
        {
            return count($this->items);
        }

        /**
         * Query item entry value
         * 
         * @param mixed $ident The ident of the object
         * @return Asatru\Database\Collection|mixed The value of the item, can be a Collection, too
         */
        public function get($ident)
        {
            if (isset($this->items[$ident])) {
                return $this->items[$ident];
            }

            return null;
        }

        /**
         * Iterate through entries and inform a callback function
         * 
         * @param closure $callback The function to be called for each item
         * @param array $data optional A key-value paired array containing data to pass to the callback function
         * @return void
         */
        public function each($callback, array $data = null)
        {
            foreach ($this->items as $ident => $item) {
                if ($data !== null) {
                    call_user_func_array($callback, array($ident, $item, $data));
                } else {
                    call_user_func_array($callback, array($ident, $item));
                }
            }
        }
    }

    /**
     * This component handles the models
     */
    abstract class Model {
        private static $instance = null;
        private static $handle = null;
        private static $where = '';
        private static $limit = '';
        private static $orderBy = '';
        private static $groupBy = '';
        private static $aggregate = '';
        private static $update = '';
        private static $insert = array();
        private static $getcount = false;
        private static $params = array();

        /**
         * Get singleton instance (create if not exists)
         * 
         * @return Asatru\Database\Model
         */
        public static function getInstance()
        {
            if (self::$instance === null) {
                self::$instance = new static;
            }

            return self::$instance;
        }

        /**
         * Set PDO handle
         * 
         * @param \PDO $pdo
         * @return void
         */
        public static function __setHandle($pdo)
        {
            self::$handle = $pdo;
        }

        /**
         * Determine the parameter type
         * 
         * @param string $param The object to be checked
         * @return The constant specifying the data type
         */
        public static function getParamType($param)
        {
            switch (gettype($param)) {
                case 'boolean':
                    return \PDO::PARAM_BOOL;
                    break;
                case 'integer':
                    return \PDO::PARAM_INT;
                    break;
                case 'NULL':
                    return \PDO::PARAM_NULL;
                    break;
                case 'string':
                    return \PDO::PARAM_STR;
                    break;
                case 'double':
                    return \PDO::PARAM_STR;
                    break;
            }

            return \PDO::PARAM_STR;
        }

        /**
         * Perform database raw operation
         * 
         * @param string $qry The SQL query string
         * @param array $opt optional A key-value paired array with the arguments of the SQL query string
         * @return mixed|boolean The return type depends of the type of query, or false on failure
         * @throws \Exception
         */
        public static function raw($qry, $opt = null)
        {
            if (!self::$handle) {
                throw new \Exception('PDO connection must be provided first');
            }

            $prp = self::$handle->prepare($qry);

            if ($opt !== null) {
                foreach ($opt as $key => $item) {
                    $prp->bindValue($key + 1, $item, self::getParamType($item));
                }
            }

            $prp->execute();

            $error = self::$handle->errorInfo();
            if ($error[0] !== '00000') {
                throw new \Exception('SQL error: ' . $error[0] . ':' . $error[1] . ' -> ' . $error[2]);
            }

            $opResult = $prp->fetchAll();
            
            if (self::$getcount === true) {
                return intval($opResult[0]['count']);
            } else if (self::$update !== '') {
                return ($error[0] === '00000') ? true : false;
            } else if (count(self::$insert) > 0) {
                return ($error[0] === '00000') ? true : false;
            } else if (strpos($qry, 'DELETE') === 0) {
                return ($error[0] === '00000') ? true : false;
            } else {
                return new Collection($opResult);
            }

            return false;
        }

        /**
         * Query all entries
         * 
         * @return Asatru\Database\Collection|boolean
         */
        public static function all()
        {
            return self::raw('SELECT * FROM ' . static::tableName());
        }

        /**
         * Flag that we shall only get the count
         * 
         * @return Asatru\Database\Model
         */
        public static function count()
        {
            self::$getcount = true;

            return self::getInstance();
        }

        /**
         * Find entry by id
         * 
         * @param mixed The ID of the item
         * @param string $key optional The name of the column to look for
         * @return Asatru\Database\Collection|boolean
         */
        public static function find($id, $key = 'id')
        {
            $query = 'SELECT * FROM ' . static::tableName() . ' WHERE ' . $key . ' = ?';

            return self::raw($query, array($id));
        }

        /**
         * Create a and-where clause
         * 
         * @param string $name The name of the column
         * @param string $comparison The type of the comparision to be performed
         * @param mixed $value The value to be checked
         * @return Asatru\Database\Model
         */
        public static function where($name, $comparison, $value)
        {
            if (self::$where === '') {
                self::$where = 'WHERE ' . $name . ' ' . $comparison . ' ?';
            } else {
                self::$where .= ' AND ' . $name . ' ' . $comparison . ' ?';
            }

            array_push(self::$params, $value);

            return self::getInstance();
        }

        /**
         * Create a or-where clause
         * 
         * @param string $name The name of the column
         * @param string $comparison The type of the comparision to be performed
         * @param mixed $value The value to be checked
         * @return Asatru\Database\Model
         */
        public static function whereOr($name, $comparison, $value)
        {
            if (self::$where === '') {
                self::$where = 'WHERE ' . $name . ' ' . $comparison . ' ?';
            } else {
                self::$where .= ' OR ' . $name . ' ' . $comparison . ' ?';
            }

            array_push(self::$params, $value);

            return self::getInstance();
        }

        /**
         * Create a and-where between clause
         * 
         * @param string $name The name of the column
         * @param int $value1 The inclusive minimum value
         * @param int $value2 The inclusive maximum value
         * @return Asatru\Database\Model
         */
        public static function whereBetween($name, $value1, $value2)
        {
            if (self::$where === '') {
                self::$where = 'WHERE ' . $name . ' BETWEEN ? AND ?';
            } else {
                self::$where .= ' AND ' . $name . ' BETWEEN ? and ?';
            }

            array_push(self::$params, $value1);
            array_push(self::$params, $value2);

            return self::getInstance();
        }

        /**
         * Create a or-where between clause
         * 
         * @param string $name The name of the column
         * @param int $value1 The inclusive minimum value
         * @param int $value2 The inclusive maximum value
         * @return Asatru\Database\Model
         */
        public static function whereBetweenOr($name, $value1, $value2)
        {
            if (self::$where === '') {
                self::$where = 'WHERE ' . $name . ' BETWEEN ? AND ?';
            } else {
                self::$where .= ' OR ' . $name . ' BETWEEN ? and ?';
            }

            array_push(self::$params, $value1);
            array_push(self::$params, $value2);

            return self::getInstance();
        }

        /**
         * Create a limit clause
         * 
         * @param int $value The value of the limit
         * @return Asatru\Database\Model
         */
        public static function limit($value)
        {
            if (self::$limit === '') {
                self::$limit = 'LIMIT ?';
                array_push(self::$params, $value);
            }

            return self::getInstance();
        }

        /**
         * Create an ordering clause
         * 
         * @param string $ident The column name
         * @param string $type The type of the ordering
         * @return Asatru\Database\Model
         */
        public static function orderBy($ident, $type)
        {
            if (self::$orderBy === '') {
                self::$orderBy = 'ORDER BY ? ?';
                array_push(self::$params, $ident);
                array_push(self::$params, $type);
            }

            return self::getInstance();
        }

        /**
         * Create a group-by clause
         * 
         * @param string $ident The column to be grouped by
         * @return Asatru\Database\Model
         */
        public static function groupBy($ident)
        {
            if (self::$groupBy === '') {
                self::$groupBy = 'GROUP BY ?';
                array_push(self::$params, $ident);
            }

            return self::getInstance();
        }

        /**
         * Add an aggregate query
         * 
         * @param string $type The aggregate identifier
         * @param string $column The column to be passed as argument
         * @param string|null $name optional A name to be used as the result variable or null
         * @return Asatru\Database\Model
         */
        public static function aggregate($type, $column, $name = null)
        {
            if ($name === null) {
                $name = $column;
            }

            if (self::$aggregate === '') {
                self::$aggregate = $type . '(' . $column . ') as ' . $name;
            } else {
                self::$aggregate .= ', ' . $type . '(' . $column . ') as ' . $name;
            }

            return self::getInstance();
        }

        /**
         * Perform database query and get first entry
         * 
         * @return Asatru\Database\Collection|boolean
         */
        public static function first()
        {
            $query = 'SELECT * FROM ' . static::tableName() . ' ' . self::$where . ' ' . self::$groupBy . ' ' . self::$orderBy . ' LIMIT 1';

            $result = self::raw($query, self::$params);

            self::$where = '';
            self::$groupBy = '';
            self::$orderBy = '';
            self::$aggregate = '';
            self::$params = array();

            return $result;
        }

        /**
         * Perform database query
         * 
         * @return Asatru\Database\Collection|boolean
         */
        public static function get()
        {
            $select = '';
            if (self::$getcount !== false) {
                $select = 'COUNT(*) as count';
            } else if (self::$aggregate !== '') {
                $select = self::$aggregate;
            } else {
                $select = '*';
            }

            $query = 'SELECT ' . $select . ' FROM ' . static::tableName() . ' ' . self::$where . ' '  . self::$groupBy . ' ' . self::$orderBy . ' ' . self::$limit;

            $result = self::raw($query, self::$params);

            self::$where = '';
            self::$groupBy = '';
            self::$orderBy = '';
            self::$limit = '';
            self::$aggregate = '';
            self::$getcount = false;
            self::$params = array();

            return $result;
        }

        /**
         * Create update set clause
         * 
         * @param string $ident The column name
         * @param mixed $value The value
         * @return Asatru\Database\Model
         */
        public static function update($ident, $value)
        {
            if (self::$update === '') {
                self::$update = 'SET ' . $ident . ' = ?';
            } else {
                self::$update .= ', ' . $ident . ' = ?';
            }

            array_push(self::$params, $value);

            return self::getInstance();
        }

        /**
         * Add to insert array
         * 
         * @param string $ident The column name
         * @param mixed $value The value
         * @return Asatru\Database\Model
         */
        public static function insert($ident, $value)
        {
            $item = array('ident' => $ident, 'value' => $value);
            array_push(self::$insert, $item);

            return self::getInstance();
        }

        /**
         * Perform database query. Either update or insert
         * 
         * @return mixed|boolean Result depends on the result of raw() or false on failure
         */
        public static function go()
        {
            if (self::$update !== '') {
                $query = 'UPDATE ' . static::tableName() . ' ' . self::$update . ' ' . self::$where;
                $result = static::raw($query, self::$params);

                self::$where = '';
                self::$update = '';
                self::$params = array();

                return $result;
            } else if (count(self::$insert) > 0) {
                $idents = '(';
                $values = 'VALUES(';

                foreach (self::$insert as $value) {
                    $idents .= $value['ident'] . ',';
                    $values .= '?,';

                    array_push(self::$params, $value['value']);
                }

                $idents = substr($idents, 0, strlen($idents)-1) . ')';
                $values = substr($values, 0, strlen($values)-1) . ')';

                $query = 'INSERT INTO ' . static::tableName() . ' ' . $idents . ' ' . $values;
                $result = static::raw($query, self::$params);

                self::$insert = array();
                self::$params = array();

                return $result;
            }

            return false;
        }

        /**
         * Perform a deletion statement
         * 
         * @return mixed Depends on the result of raw()
         */
        public static function delete()
        {
            $query = 'DELETE FROM ' . static::tableName() . ' ' . self::$where;

            $result = self::raw($query, self::$params);

            self::$where = '';
            self::$params = array();

            return $result;
        }

        //Return the associated database table
        abstract public static function tableName();
    }

    /**
     * This component loads all migrations
     */
    class MigrationLoader {
        private $handle = null;

        /**
         * Set handle
         * 
         * @param \PDO $pdo The handle to the PDO instance
         * @return void
         */
        public function __construct($pdo)
        {
            $this->handle = $pdo;
        }

        /**
         * Load migration list
         * 
         * @return array An array containing each hashes
         */
        private function loadMigrationList()
        {
            if (!file_exists(ASATRU_APP_ROOT . '/app/migrations/migrations.list')) {
                return array();
            }

            return preg_split('/(\r\n|\n|\r)/', file_get_contents(ASATRU_APP_ROOT . '/app/migrations/migrations.list'));
        }

        /**
         * Store migration list
         * 
         * @param array An array containing the hashes
         * @return boolean
         */
        private function storeMigrationList($migrations)
        {
            $content = '';

            foreach ($migrations as $migration) {
                $content .= $migration . PHP_EOL;
            }

            return file_put_contents(ASATRU_APP_ROOT . '/app/migrations/migrations.list', $content) !== false;
        }

        /**
         * Check if in migration list
         * 
         * @param array $list The migration list
         * @param string $file The hash of the file
         * @return boolean
         */
        private function isInMigrationList($list, $file)
        {
            foreach ($list as $entry) {
                if ($entry === $file) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Load all migrations if not on the list and put a migrated entity to list
         * 
         * @return void
         * @throws \Exception
         */
        public function createAll()
        {
            $files = scandir(ASATRU_APP_ROOT . '/app/migrations');
            if ($files === false) {
                throw new \Exception('Migration folder not found');
            }

            $list = $this->loadMigrationList();

            foreach ($files as $file) {
                if (pathinfo(ASATRU_APP_ROOT . '/app/migrations/' . $file, PATHINFO_EXTENSION) === 'php') {
                    if (!$this->isInMigrationList($list, hash('sha512', $file))) {
                        require_once ASATRU_APP_ROOT . '/app/migrations/' . $file;

                        $className = ucfirst(pathinfo(ASATRU_APP_ROOT . '/app/migrations/' . $file, PATHINFO_FILENAME)) . '_Migration';
                        $obj = new $className($this->handle);

                        if (method_exists($obj, 'up')) {
                            $result = call_user_func(array($obj, 'up'));
                        } else {
                            throw new \Exception('method up() not found in migration ' . $className);
                        }

                        array_push($list, hash('sha512', $file));
                    }
                }
            }

            $this->storeMigrationList($list);
        }

        /**
         * Drop all migrations
         * 
         * @return void
         * @throws \Exception
         */
        public function dropAll()
        {
            $files = scandir(ASATRU_APP_ROOT . '/app/migrations');
            if ($files === false) {
                throw new \Exception('Migration folder not found');
            }

            foreach ($files as $file) {
                if (pathinfo(ASATRU_APP_ROOT . '/app/migrations/' . $file, PATHINFO_EXTENSION) === 'php') {
                    require_once ASATRU_APP_ROOT . '/app/migrations/' . $file;
                    
                    $className = ucfirst(pathinfo(ASATRU_APP_ROOT . '/app/migrations/' . $file, PATHINFO_FILENAME)) . '_Migration';
                    $obj = new $className($this->handle);
                    
                    if (method_exists($obj, 'down')) {
                        call_user_func(array($obj, 'down'));
                    } else {
                        throw new \Exception('method down() not found in migration ' . $className);
                    }
                }
            }

            if (file_exists(ASATRU_APP_ROOT . '/app/migrations/migrations.list')) {
                unlink(ASATRU_APP_ROOT . '/app/migrations/migrations.list');
            }
        }
    }
}

namespace {
    if ((isset($_ENV['DB_ENABLE'])) && ($_ENV['DB_ENABLE'])) {
        //Instantiate PDO connection
        if (!isset($_ENV['DB_DRIVER'])) {
            throw new \Exception('No database PDO driver specified');
        } else if ($_ENV['DB_DRIVER'] === 'mysql') {
            $objPdo = new \PDO('mysql:host=' . $_ENV['DB_HOST'] . ';port=' . $_ENV['DB_PORT'] . ';dbname=' . $_ENV['DB_DATABASE'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        } else {
            throw new \Exception('Database driver ' . $_ENV['DB_DRIVER'] . ' is not supported');
        }

        //Instantiate migration loader
        $objMigrationLoader = new Asatru\Database\MigrationLoader($objPdo);

        //Include all models
        $models = scandir(ASATRU_APP_ROOT . '/app/models');
        if ($models !== false) {
            foreach ($models as $file) {
                if (pathinfo(ASATRU_APP_ROOT . '/app/models/' . $file, PATHINFO_EXTENSION) == 'php') {
                    require_once ASATRU_APP_ROOT . '/app/models/' . $file;

                    $className = pathinfo($file, PATHINFO_FILENAME);
                    $className::__setHandle($objPdo);
                }
            }
        }
    
        /**
         * Create function for fresh migration
         * 
         * @return void
         */
        function migrate_fresh()
        {
            global $objPdo;
            global $objMigrationLoader;

            $objMigrationLoader->dropAll();
            $objMigrationLoader->createAll();
        }

        /**
         * Create function for listed migration
         * 
         * @return void
         */
        function migrate_list()
        {
            global $objPdo;
            global $objMigrationLoader;

            $objMigrationLoader->createAll();
        }

        /**
         * Create function for dropping all migrations
         * 
         * @return void
         */
        function migrate_drop()
        {
            global $objPdo;
            global $objMigrationLoader;

            $objMigrationLoader->dropAll();
        }
    }
}