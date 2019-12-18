<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

namespace Asatru\Database {
    //This component handles the table creation
    class Migration {
        private $handle = null;
        private $command = null;
        private $name = null;

        public function __construct($name, $con)
        {
            //Initialize table creation

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

        public function add($column)
        {
            //Add new column

            $this->command .= (($this->command[strlen($this->command)-1] !== '(') ? ', ' : '') . $column;
        }

        public function append($column)
        {
            //Append new column

            $this->handle->exec('ALTER TABLE ' . $this->name . ' ADD ' . $column . ';');
        }

        public function create()
        {
            //Create table

            $this->command .= ');';

            $this->handle->exec($this->command);

            $error = $this->handle->errorInfo();
            if ($error[0] !== '00000') {
                throw new \Exception('SQL error: ' . $error[0] . ':' . $error[1] . ' -> ' . $error[2]);
            }
        }

        public function drop()
        {
            //Drop the table
            
            $this->handle->exec('DROP TABLE IF EXISTS ' . $this->name . ';');

            $error = $this->handle->errorInfo();
            if ($error[0] !== '00000') {
                throw new \Exception('SQL error: ' . $error[0] . ':' . $error[1] . ' -> ' . $error[2]);
            }
        }

        public function getTableName()
        {
            //Get table name

            return $this->name;
        }
    }

    //This component represents a database query result collection
    class Collection {
        private $items = array();

        public function __construct($arr)
        {
            //Construct object

            $this->createFromArray($arr);
        }

        private function createFromArray($arr)
        {
            //Create collection from array
            
            foreach ($arr as $key => $value) {
                if (is_array($value)) {
                    $this->items[$key] = new Collection($value);
                } else {
                    $this->items[$key] = $value;
                }
            }
        }

        public function count()
        {
            //Return amount of items

            return count($this->items);
        }

        public function get($ident)
        {
            //Query item entry value

            if (isset($this->items[$ident])) {
                return $this->items[$ident];
            }

            return null;
        }

        public function each($callback)
        {
            //Iterate through entries and inform a callback function

            foreach ($this->items as $ident => $item) {
                call_user_func_array($callback, array($ident, $item));
            }
        }
    }

    //This component handles the models
    abstract class Model {
        private static $instance = null;
        private static $handle = null;
        private static $where = '';
        private static $whereBetween = '';
        private static $limit = '';
        private static $orderBy = '';
        private static $groupBy = '';
        private static $aggregate = '';
        private static $update = '';
        private static $insert = array();
        private static $getcount = false;
        private static $params = array();

        private static function getInstance()
        {
            //Get singleton instance (create if not exists)

            if (self::$instance === null) {
                self::$instance = new static;
            }

            return self::$instance;
        }

        public static function __setHandle($pdo)
        {
            //Set PDO handle
            
            self::$handle = $pdo;
        }

        public static function getParamType($param)
        {
            //Determin the parameter type

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

        public static function raw($qry, $opt = null)
        {
            //Perform database raw operation

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
                return $opResult[0]['count'];
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

        public static function all()
        {
            //Query all entries

            return self::raw('SELECT * FROM ' . static::tableName());
        }

        public static function count()
        {
            //Flag that we shall only get the count

            self::$getcount = true;

            return self::getInstance();
        }

        public static function find($id, $key = 'id')
        {
            //Find entry by id

            $query = 'SELECT * FROM ' . static::tableName() . ' WHERE ' . $key . ' = ?';

            return self::raw($query, array($id));
        }

        public static function where($name, $comparison, $value)
        {
            //Create a and-where clause

            if (self::$where === '') {
                self::$where = 'WHERE ' . $name . ' ' . $comparison . ' ?';
            } else {
                self::$where .= ' AND ' . $name . ' ' . $comparison . ' ?';
            }

            array_push(self::$params, $value);

            return self::getInstance();
        }

        public static function whereOr($name, $comparison, $value)
        {
            //Create a or-where clause

            if (self::$where === '') {
                self::$where = 'WHERE ' . $name . ' ' . $comparison . ' ?';
            } else {
                self::$where .= ' OR ' . $name . ' ' . $comparison . ' ?';
            }

            array_push(self::$params, $value);

            return self::getInstance();
        }

        public static function whereBetween($name, $value1, $value2)
        {
            //Create a and-where between clause

            if (self::$whereBetween === '') {
                self::$whereBetween = 'WHERE ' . $name . ' BETWEEN ? AND ?';
            } else {
                self::$whereBetween .= ' AND ' . $name . ' BETWEEN ? and ?';
            }

            array_push(self::$params, $value1);
            array_push(self::$params, $value2);

            return self::getInstance();
        }

        public static function whereBetweenOr($name, $value1, $value2)
        {
            //Create a or-where between clause

            if (self::$whereBetween === '') {
                self::$whereBetween = 'WHERE ' . $name . ' BETWEEN ? AND ?';
            } else {
                self::$whereBetween .= ' OR ' . $name . ' BETWEEN ? and ?';
            }

            array_push(self::$params, $value1);
            array_push(self::$params, $value2);

            return self::getInstance();
        }

        public static function limit($value)
        {
            //Create a limit clause

            if (self::$limit === '') {
                self::$limit = 'LIMIT ?';
                array_push(self::$params, $value);
            }

            return self::getInstance();
        }

        public static function orderBy($ident, $type)
        {
            //Create an ordering clause

            if (self::$orderBy === '') {
                self::$orderBy = 'ORDER BY ? ?';
                array_push(self::$params, $ident);
                array_push(self::$params, $type);
            }

            return self::getInstance();
        }

        public static function groupBy($ident)
        {
            //Create a group-by clause

            if (self::$groupBy === '') {
                self::$groupBy = 'GROUP BY ?';
                array_push(self::$params, $ident);
            }

            return self::getInstance();
        }

        public static function aggregate($type, $column, $name = null)
        {
            //Add an aggregate query

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

        public static function first()
        {
            //Perform database query and get first entry

            $query = 'SELECT * FROM ' . static::tableName() . ' ' . self::$where . ' ' . self::$whereBetween . ' ' . self::$groupBy . ' ' . self::$orderBy . ' LIMIT 1';

            $result = self::raw($query, self::$params);

            self::$where = '';
            self::$whereBetween = '';
            self::$groupBy = '';
            self::$orderBy = '';
            self::$aggregate = '';
            self::$params = array();

            return $result;
        }

        public static function get()
        {
            //Perform database query

            $select = '';
            if (self::$getcount !== false) {
                $select = 'COUNT(*) as count';
            } else if (self::$aggregate !== '') {
                $select = self::$aggregate;
            } else {
                $select = '*';
            }

            $query = 'SELECT ' . $select . ' FROM ' . static::tableName() . ' ' . self::$where . ' ' . self::$whereBetween . ' '  . self::$groupBy . ' ' . self::$orderBy . ' ' . self::$limit;

            $result = self::raw($query, self::$params);

            self::$where = '';
            self::$whereBetween = '';
            self::$groupBy = '';
            self::$orderBy = '';
            self::$limit = '';
            self::$aggregate = '';
            self::$getcount = false;
            self::$params = array();

            return $result;
        }

        public static function update($ident, $value)
        {
            //Create update set clause

            if (self::$update === '') {
                self::$update = 'SET ' . $ident . ' = ?';
            } else {
                self::$update = ', ' . $ident . ' = ?';
            }

            array_push(self::$params, $value);

            return self::getInstance();
        }

        public static function insert($ident, $value)
        {
            //Add to insert array

            $item = array('ident' => $ident, 'value' => $value);
            array_push(self::$insert, $item);

            return self::getInstance();
        }

        public static function go()
        {
            //Perform database query. Either update or insert

            if (self::$update !== '') {
                $query = 'UPDATE ' . static::tableName() . ' ' . self::$update . ' ' . self::$where;
                $result = static::raw($query, self::$params);

                self::$where = '';
                self::$update = '';
                self::$params = array();

                return $result;
            } else if (self::$insert !== '') {
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

        public static function delete()
        {
            //Delete a row

            $query = 'DELETE FROM ' . static::tableName() . ' ' . self::$where;

            $result = self::raw($query, self::$params);

            self::$where = '';
            self::$params = array();

            return $result;
        }

        //Return the associated database table
        abstract public static function tableName();
    }

    //This component loads all migrations
    class MigrationLoader {
        private $handle = null;

        public function __construct($pdo)
        {
            //Set handle

            $this->handle = $pdo;
        }

        private function loadMigrationList()
        {
            //Load migration list

            if (!file_exists(__DIR__ . '/../../../../app/migrations/migrations.list')) {
                return array();
            }

            return preg_split('/(\r\n|\n|\r)/', file_get_contents(__DIR__ . '/../../../../app/migrations/migrations.list'));
        }

        private function storeMigrationList($migrations)
        {
            //Store migration list

            $content = '';

            foreach ($migrations as $migration) {
                $content .= $migration . PHP_EOL;
            }

            return file_put_contents(__DIR__ . '/../../../../app/migrations/migrations.list', $content) !== false;
        }

        private function isInMigrationList($list, $file)
        {
            //Check if in migration list

            foreach ($list as $entry) {
                if ($entry === $file) {
                    return true;
                }
            }

            return false;
        }

        public function createAll()
        {
            //Load all migrations if not on the list and put a migrated entity to list

            $files = scandir(__DIR__ . '/../../../../app/migrations');
            if ($files === false) {
                throw new Exception('Migration folder not found');
            }

            $list = $this->loadMigrationList();

            foreach ($files as $file) {
                if (pathinfo(__DIR__ . '/../../../../app/migrations/' . $file, PATHINFO_EXTENSION) === 'php') {
                    if (!$this->isInMigrationList($list, hash('sha512', $file))) {
                        require_once __DIR__ . '/../../../../app/migrations/' . $file;

                        $className = ucfirst(pathinfo(__DIR__ . '/../../../../app/migrations/' . $file, PATHINFO_FILENAME)) . '_Migration';
                        $obj = new $className($this->handle);

                        if (method_exists($obj, 'up')) {
                            $result = call_user_func(array($obj, 'up'));
                        } else {
                            throw new Exception('method up() not found in migration ' . $className);
                        }

                        array_push($list, hash('sha512', $file));
                    }
                }
            }

            $this->storeMigrationList($list);
        }

        public function dropAll()
        {
            //Drop all migrations

            $files = scandir(__DIR__ . '/../../../../app/migrations');
            if ($files === false) {
                throw new Exception('Migration folder not found');
            }

            foreach ($files as $file) {
                if (pathinfo(__DIR__ . '/../../../../app/migrations/' . $file, PATHINFO_EXTENSION) === 'php') {
                    require_once __DIR__ . '/../../../../app/migrations/' . $file;
                    
                    $className = ucfirst(pathinfo(__DIR__ . '/../../../../app/migrations/' . $file, PATHINFO_FILENAME)) . '_Migration';
                    $obj = new $className($this->handle);
                    
                    if (method_exists($obj, 'down')) {
                        call_user_func(array($obj, 'down'));
                    } else {
                        throw new Exception('method down() not found in migration ' . $className);
                    }
                }
            }

            if (file_exists(__DIR__ . '/../../../../app/migrations/migrations.list')) {
                unlink(__DIR__ . '/../../../../app/migrations/migrations.list');
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
        } else if ($_ENV['DB_DRIVER'] === 'sqlite') {
            $objPdo = new \PDO('sqlite:' . __DIR__ . '/../../../../app/db/' . $_ENV['DB_DATABASE']);
        } else if ($_ENV['DB_DRIVER'] === 'oracle') {
            $objPdo = new \PDO('oci:dbname=' . $_ENV['DB_DATABASE'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        } else if ($_ENV['DB_DRIVER'] === 'mssql') {
            $objPdo = new \PDO('sqlsrv:Server=' . $_ENV['DB_HOST'] . ':'. $_ENV['DB_PORT'] . ';Database=' . $_ENV['DB_DATABASE'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        } else {
            throw new \Exception('Database driver ' . $_ENV['DB_DRIVER'] . ' is not supported');
        }

        //Instantiate migration loader
        $objMigrationLoader = new Asatru\Database\MigrationLoader($objPdo);

        //Include all models
        $models = scandir(__DIR__ . '/../../../../app/models');
        if ($models !== false) {
            foreach ($models as $file) {
                if (pathinfo(__DIR__ . '/../../../../app/models/' . $file, PATHINFO_EXTENSION) == 'php') {
                    require_once __DIR__ . '/../../../../app/models/' . $file;

                    $className = pathinfo($file, PATHINFO_FILENAME);
                    $className::__setHandle($objPdo);
                }
            }
        }
    
        //Create function for fresh migration
        function migrate_fresh()
        {
            global $objPdo;
            global $objMigrationLoader;

            $objMigrationLoader->dropAll();
            $objMigrationLoader->createAll();
        }

        //Create function for listed migration
        function migrate_list()
        {
            global $objPdo;
            global $objMigrationLoader;

            $objMigrationLoader->createAll();
        }

        //Create function for dropping all migrations
        //Create function for listed migration
        function migrate_drop()
        {
            global $objPdo;
            global $objMigrationLoader;

            $objMigrationLoader->dropAll();
        }
    }
}