<?php

class DBM {

    /**
     * Automatically add/update created/updated fields
     *
     * @var boolean
     */
    public static $timestamp_writes = false;

    /**
     * Dynamic config creds
     *
     * @var Array - representing config details
     */
    protected $config;

    /**
     * The PDO objects for the connection
     *
     * @var PDO - the Pear Data Object
     */
    protected $pdo;

    /**
     * A reference to the singleton instance
     *
     * @var DBM
     */
    protected static $instance = null;

    /**
     * method instance.
     *    - static, for singleton, for creating a global instance of this object
     * @return DBM - DBM Object
     */
    public static function run() {
        if (! isset(self::$instance)) {
            self::$instance = new DBM();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     * 	- make protected so only subclasses and self can create this object (singleton)
     */
    protected function __construct() {}

    /**
     * method config
     *    - configure connection credentials to the db server
     *
     * @param $host
     * @param $name
     * @param $user
     * @param $password
     * @param null $port
     * @param string $driver
     * @throws Exception
     * @internal param $host - the host name of the db to connect to
     * @internal param $name - the database name
     * @internal param $user - the user name
     * @internal param $password - the users password
     * @internal param $port (optional) - the port to connect using, default to 3306
     * @internal param $driver - the dsn prefix
     */
    public function config($host, $name, $user, $password, $port=null, $driver='mysql') {
        if (!$this->validateDriver($driver)) {
            throw new Exception('DATABASE WRAPPER::error, the database you wish to connect to is not supported by your install of PHP.');
        }

        if (isset($this->pdo)) {
            error_log('DATABASE WRAPPER::warning, attempting to config after connection exists');
        }

        $this->config = [
            'driver' => $driver,
            'host' => $host,
            'name' => $name,
            'user' => $user,
            'password' => $password,
            'port' => $port
        ];
    }

    /**
     * method createConnection.
     *    - create a PDO connection using the credentials provided
     *
     * @param $driver
     * @param $host
     * @param $name
     * @param $user
     * @param $password
     * @param null $port
     * @return PDO object with a connection to the database specified
     * @throws Exception
     * @internal param $driver - the dsn prefix
     * @internal param $host - the host name of the db to connect to
     * @internal param $name - the database name
     * @internal param $user - the user name
     * @internal param $password - the users password
     * @internal param $port (optional) - the port to connect using, default to 3306
     */
    protected function createConnection($driver, $host, $name, $user, $password, $port=null) {
        if (!$this->validateDriver($driver)) {
            throw new Exception('DATABASE WRAPPER::error, the database you wish to connect to is not supported by your install of PHP.');
        }

        // attempt to create pdo object and connect to the database
        try {
            //@TODO the following drivers are NOT supported yet: odbc, ibm, informix, 4D
            // build the connection string from static constants based on the selected PDO Driver.
            if ($driver == "sqlite" || $driver == "sqlite2") {
                $connection_string = $driver.':'.$host;
            } elseif ($driver == "sqlsrv") {
                $connection_string = "sqlsrv:Server=".$host.";Database=".$name;
            } elseif ($driver == "firebird" || $driver == "oci") {
                $connection_string = $driver.":dbname=".$name;
            } else {
                $connection_string = $driver.':host='.$host.';dbname='.$name;
            }

            // add the port if one was specified
            if (!empty($port)) {
                $connection_string .= ";port=$port";
            }

            // initialize the PDO object
            $new_connection = new PDO($connection_string, $user, $password);

            // set the error mode
            $new_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $new_connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $new_connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

            $new_connection->exec('SET CHARACTER SET utf8');
            $new_connection->exec('SET NAMES utf8mb4');

            // return the new connection
            return $new_connection;
        }

        // handle any exceptions by catching them and returning false
        catch (PDOException $e) {
            throw $e;
        }
        catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * method get.
     * 	- grab the PDO connection to the DB
     */
    protected function get() {

        // if we have not created the db connection yet, create it now
        if (!isset($this->pdo)) {
            $this->pdo = $this->createConnection(
                $this->config['driver'],
                $this->config['host'],
                $this->config['name'],
                $this->config['user'],
                $this->config['password'],
                $this->config['port']
            );
        }

        return $this->pdo;
    }

    /**
     * Получение количества элементов в таблице
     * @param  string $table имя таблицы
     * @param  array $params массив условий
     * @return int количество записей
     * @throws Exception
     */
    public function count($table, $params = null) {
        $sql_str = "SELECT count(*) FROM $table";

        $sql_str .= ( count($params)>0 ? ' WHERE ' : '' );

        $add_and = false;
        // add each clause using parameter array
        if (empty($params)) {
            $params = [];
        }
        foreach ($params as $key=>$val) {
            // only add AND after the first clause item has been appended
            if ($add_and) {
                $sql_str .= ' AND ';
            } else {
                $add_and = true;
            }

            // append clause item
            if (is_array($val)) {
                $sql_str .= $key.current($val).":$key";
            } else {
                $sql_str .= "$key = :$key";
            }
        }

        // now we attempt to retrieve the row using the sql string
        try {

            $pstmt = $this->get()->prepare($sql_str);

            // bind each parameter in the array
            foreach ($params as $key=>$val) {
                if (is_array($val)) {
                    $val = end($val);
                }
                $pstmt->bindValue(':'.$key, $val);
            }

            $pstmt->execute();

            return $pstmt->fetchColumn(0);
        }
        catch(PDOException $e) {
            throw $e;
        }
        catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * method select.
     *    - retrieve information from the database, as an array
     *
     * @param string $table - the name of the db table we are retreiving the rows from
     * @param array $params - associative array representing the WHERE clause filters
     * @param int $limit (optional) - the amount of rows to return
     * @param int $start (optional) - the row to start on, indexed by zero
     * @param array $order_by (optional) - an array with order by clause
     * @return mixed - associate representing the fetched table row, false on failure
     * @throws Exception
     */
    public function select($table, $params = null, $limit = null, $start = null, $order_by = null) {
        // building query string
        $sql_str = "SELECT * FROM $table";
        // append WHERE if necessary
        $sql_str .= ( count($params)>0 ? ' WHERE ' : '' );

        $add_and = false;
        // add each clause using parameter array
        if (empty($params)) {
            $params = [];
        }
        foreach ($params as $key=>$val) {
            // only add AND after the first clause item has been appended
            if ($add_and) {
                $sql_str .= ' AND ';
            } else {
                $add_and = true;
            }

            // append clause item
            if (is_array($val)) {
                $sql_str .= $key.current($val).":$key";
            } else {
                $sql_str .= "$key=:$key";
            }
        }

        // add the order by clause if we have one
        if (!empty($order_by)) {
            $sql_str .= ' ORDER BY';
            $add_comma = false;
            foreach ($order_by as $column => $order) {
                if ($add_comma) {
                    $sql_str .= ', ';
                }
                else {
                    $add_comma = true;
                }
                $sql_str .= " $column $order";
            }
        }

        // now we attempt to retrieve the row using the sql string
        try {
            // decide which database we are selecting from
            $pdoDriver = $this->get()->getAttribute(PDO::ATTR_DRIVER_NAME);

            //@TODO MS SQL Server & Oracle handle LIMITs differently, for now its disabled but we should address it later.
            $disableLimit = ["sqlsrv", "mssql", "oci"];

            // add the limit clause if we have one
            if (!is_null($limit) && !in_array($pdoDriver, $disableLimit)) {
                $sql_str .= ' LIMIT '.(!is_null($start) ? "$start, ": '')."$limit";
            }

            $pstmt = $this->get()->prepare($sql_str);

            // bind each parameter in the array
            foreach ($params as $key=>$val) {
                if (is_array($val)) {
                    $val = end($val);
                }
                $pstmt->bindValue(':'.$key, $val);
            }

            $pstmt->execute();

            // now return the results, depending on if we want all or first row only
            if (! is_null($limit) && $limit == 1) {
                return $pstmt->fetch(PDO::FETCH_ASSOC);
            } else {
                return $pstmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        catch(PDOException $e) {
            throw $e;
        }
        catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * method selectFirst.
     *    - retrieve the first row returned from a select statement
     *
     * @param table - the name of the db table we are retreiving the rows from
     * @param array $params
     * @param array $order_by (optional) - an array with order by clause
     * @return mixed - associate representing the fetched table row, false on failure
     */
    public function selectFirst($table, $params = [], $order_by = null) {
        return $this->select($table, $params, 1, null, $order_by);
    }

    /**
     * method delete.
     *    - deletes rows from a table based on the parameters
     *
     * @param table - the name of the db table we are deleting the rows from
     * @param array $params
     * @return bool - associate representing the fetched table row, false on failure
     * @throws Exception
     */
    public function delete($table, $params = []) {
        // building query string
        $sql_str = "DELETE FROM $table";
        // append WHERE if necessary
        $sql_str .= count($params)>0 ? ' WHERE ' : '';

        $add_and = false;
        // add each clause using parameter array
        foreach ($params as $key=>$val) {
            // only add AND after the first clause item has been appended
            if ($add_and) {
                $sql_str .= ' AND ';
            } else {
                $add_and = true;
            }

            // append clause item
            if (is_array($val)) {
                $sql_str .= "$key".current($val).":$key";
            } else {
                $sql_str .= "$key=:$key";
            }
        }

        // now we attempt to retrieve the row using the sql string
        try {
            $pstmt = $this->get()->prepare($sql_str);

            // bind each parameter in the array
            foreach ($params as $key=>$val) {
                if (is_array($val)) {
                    $val = end($val);
                }
                $pstmt->bindValue(':'.$key, $val);
            }

            // execute the delete query
            $successful_delete = $pstmt->execute();

            // if we were successful, return the amount of rows updated, otherwise return false
            return ($successful_delete == true) ? $pstmt->rowCount() : false;
        }
        catch(PDOException $e) {
            throw $e;
        }
        catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * method update.
     *    - updates a row to the specified table
     *
     * @param string $table - the name of the db table we are adding row to
     * @param array $params - associative array representing the columns and their respective values to update
     * @param array $wheres (Optional) - the where clause of the query
     * @param bool $timestamp_this (Optional) - if true we set created and modified values to now
     * @return bool|int - the amount of rows updated, false on failure
     * @throws Exception
     */
    public function update($table, $params, $wheres = [], $timestamp_this = false) {
        if (! $timestamp_this) {
            $timestamp_this = self::$timestamp_writes;
        }
        // build the set part of the update query by
        // adding each parameter into the set query string
        $add_comma = false;
        $set_string = '';
        foreach ($params as $key=>$val) {
            // only add comma after the first parameter has been appended
            if ($add_comma) {
                $set_string .= ', ';
            } else {
                $add_comma = true;
            }

            // now append the parameter
            if (is_array($val)) {
                $set_string .= "$key=$key".implode($val);
            } else {
                $set_string .= "$key=:param_$key";
            }
        }

        // add the timestamp columns if neccessary
        if ($timestamp_this) {
            $set_string .= ($add_comma ? ', ' : '') . 'modified='.time();
        }

        // lets add our where clause if we have one
        $where_string = '';
        if (!empty($wheres)) {
            // load each key value pair, and implode them with an AND
            $where_array = [];
            foreach($wheres as $key => $val) {

                // append clause item
                if (is_array($val)) {
                    $where_array[] = $key.current($val).":where_$key";
                } else {
                    $where_array[] = "$key=:where_$key";
                }
            }
            // build the final where string
            $where_string = 'WHERE '.implode(' AND ', $where_array);
        }

        // build final update string
        $sql_str = "UPDATE $table SET $set_string $where_string";

        // now we attempt to write this row into the database
        try {
            $pstmt = $this->get()->prepare($sql_str);

            // bind each parameter in the array
            foreach ($params as $key=>$val) {
                if (is_array($val)) continue;
                $pstmt->bindValue(':param_'.$key, $val);
            }

            // bind each where item in the array
            foreach ($wheres as $key=>$val) {
                if (is_array($val)) {
                    $val = end($val);
                }
                $pstmt->bindValue(':where_'.$key, $val);
            }

            // execute the update query
            $successful_update = $pstmt->execute();

            // if we were successful, return the amount of rows updated, otherwise return false
            return ($successful_update == true) ? $pstmt->rowCount() : false;
        }
        catch(PDOException $e) {
            throw $e;
        }
        catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * method insert.
     *    - adds a row to the specified table
     *
     * @param string $table - the name of the db table we are adding row to
     * @param array $params - associative array representing the columns and their respective values
     * @param bool $timestamp_this (Optional), if true we set created and modified values to now
     * @return mixed - new primary key of inserted table, false on failure
     * @throws Exception
     */
    public function insert($table, $params = [], $timestamp_this = false) {
        if (! $timestamp_this) {
            $timestamp_this = self::$timestamp_writes;
        }

        // first we build the sql query string
        $columns_str = '(';
        $values_str = 'VALUES (';
        $add_comma = false;

        // add each parameter into the query string
        foreach ($params as $key=>$val) {
            // only add comma after the first parameter has been appended
            if ($add_comma) {
                $columns_str .= ', ';
                $values_str .= ', ';
            } else {
                $add_comma = true;
            }

            // now append the parameter
            $columns_str .= "$key";
            $values_str .= ":$key";
        }

        // add the timestamp columns if neccessary
        if ($timestamp_this) {
            $columns_str .= ($add_comma ? ', ' : '') . 'created, modified';
            $values_str .= ($add_comma ? ', ' : '') . time().', '.time();
        }

        // close the builder strings
        $columns_str .= ') ';
        $values_str .= ')';

        // build final insert string
        $sql_str = "INSERT INTO $table $columns_str $values_str";

        // now we attempt to write this row into the database
        try {
            $pstmt = $this->get()->prepare($sql_str);

            // bind each parameter in the array
            foreach ($params as $key=>$val) {
                $pstmt->bindValue(':'.$key, $val);
            }

            $pstmt->execute();
            $newID = $this->get()->lastInsertId();

            // return the new id
            return $newID;
        }
        catch(PDOException $e) {
            throw $e;
        }
        catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * method insertMultiple.
     *    - adds multiple rows to a table with a single query
     *
     * @param string $table - the name of the db table we are adding row to
     * @param array $columns - contains the column names
     * @param array $rows
     * @param bool $timestamp_these (Optional), if true we set created and modified values to NOW() for each row
     * @return mixed - new primary key of inserted table, false on failure
     * @throws Exception
     */
    public function insertMultiple($table, $columns = [], $rows = [], $timestamp_these = false) {
        if (! $timestamp_these) {
            $timestamp_these = self::$timestamp_writes;
        }

        // generate the columns portion of the insert statment
        // adding the timestamp fields if needs be
        if ($timestamp_these) {
            $columns[] = 'created';
            $columns[] = 'modified';
        }
        $columns_str = '(' . implode(',', $columns) . ') ';

        // generate the values portions of the string
        $values_str = 'VALUES ';
        $add_comma = false;

        foreach ($rows as $row_index => $row_values) {
            // only add comma after the first row has been added
            if ($add_comma) {
                $values_str .= ', ';
            } else {
                $add_comma = true;
            }

            // here we will create the values string for a single row
            $values_str .= '(';
            $add_comma_forvalue = false;
            foreach ($row_values as $value_index => $value) {
                if ($add_comma_forvalue) {
                    $values_str .= ', ';
                } else {
                    $add_comma_forvalue = true;
                }
                // generate the bind variable name based on the row and column index
                $values_str .= ':'.$row_index.'_'.$value_index;
            }
            // append timestamps if necessary
            if ($timestamp_these) {
                $values_str .= ($add_comma_forvalue ? ', ' : '') . time().', '.time();
            }
            $values_str .= ')';
        }

        // build final insert string
        $sql_str = "INSERT INTO $table $columns_str $values_str";

        // now we attempt to write this multi inster query to the database using a transaction
        try {
            $this->get()->beginTransaction();
            $pstmt = $this->get()->prepare($sql_str);

            // traverse the 2d array of rows and values to bind all parameters
            foreach ($rows as $row_index => $row_values) {
                foreach ($row_values as $value_index => $value) {
                    $pstmt->bindValue(':'.$row_index.'_'.$value_index, $value);
                }
            }

            // now lets execute the statement, commit the transaction and return
            $pstmt->execute();
            $this->get()->commit();
            return true;
        }
        catch(PDOException $e) {
            $this->get()->rollBack();
            throw $e;
        }
        catch(Exception $e) {
            $this->get()->rollBack();
            throw $e;
        }
    }

    /**
     * method execute.
     *    - executes a query that modifies the database
     *
     * @param string $query - the SQL query we are executing
     * @param array $params
     * @return mixed - the affected rows, false on failure
     * @throws Exception
     */
    public function execute($query, $params = []) {
        try {
            // prepare the statement
            $pstmt = $this->get()->prepare($query);

            // bind each parameter in the array
            foreach ((array)$params as $key=>$val) {
                $pstmt->bindValue(':'.$key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }

            // execute the query
            $result = $pstmt->execute();

            // only if return value is false did this query fail
            return ($result == true) ? $pstmt->rowCount() : false;
        }
        catch(PDOException $e) {
            throw $e;
        }
        catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * method query.
     *    - returns data from a free form select query
     *
     * @param string $query - the SQL query we are executing
     * @param array $params - a list of bind parameters
     * @return mixed - the affected rows, false on failure
     * @throws Exception
     */
    public function query($query, $params = []) {
        try {

            $pstmt = $this->get()->prepare($query);

            // bind each parameter in the array
            foreach ((array)$params as $key=>$val) {
                $pstmt->bindValue(':'.$key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }

            // execute the query
            $pstmt->execute();

            // now return the results
            return $pstmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {
            throw $e;
        }
        catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * method queryFirst.
     * 	- returns the first record from a free form select query
     *
     * @param string $query - the SQL query we are executing
     * @param array $params - a list of bind parameters
     * @return mixed - the affected rows, false on failure
     */
    public function queryFirst($query, $params = []) {
        $result = $this->query($query, $params);
        if (empty($result)) {
            return false;
        }
        else {
            return $result[0];
        }
    }

    /**
     * Validate the database in question is supported by your installation of PHP.
     * @param string $driver The DSN prefix
     * @return boolean true, the database is supported; false, the database is not supported.
     */
    private function validateDriver($driver) {
        if (!in_array($driver, PDO::getAvailableDrivers())) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Destructor.
     * 	- release the PDO db connections
     */
    function __destruct() {
        unset($this->pdo);
    }
}
