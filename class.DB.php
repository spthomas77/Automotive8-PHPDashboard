<?php

/**
 * DB Class to interface with a MySQL database.
 *
 * <b>The following examples show how to use the DB Class.</b>
 * <code>
 * require_once('com/MindfireInc/Database/class.DB.php');
 *
 * // These already default to the correct settings for this server.
 * DB::$SERVER = 'server_name';
 * DB::$DATABASE = 'database_name';
 * DB::$USERNAME = 'database_password';
 * DB::$PASSWORD = 'database_username';
 *
 * // Binds a variable as an integer. Returns one row object.
 * $rowObject = DB::QueryExecute('SELECT * FROM table WHERE id = ? LIMIT 1', 100);
 *
 * // Binds two variables as a string and bool. Returns an array of row objects.
 * $rows = DB::QueryExecuteMulti('SELECT * FROM table WHERE last_name = ? AND active = ?', 'Smith', true);
 *
 * DB::QueryExecute('INSERT INTO table VALUES (?, ?, ?, ?, ?)', 'This is a string.', true, null, 23.33, 5);
 *
 * // Offers more control over variable binding.
 * $db = DB::Query('INSERT INTO table VALUES (?, ?, ?, ?, ?)')->
 *         bindParams(DB::PARAM_STRING, DB::PARAM_INT, DB::PARAM_FLOAT, DB::PARAM_BOOL, DB::PARAM_NULL)->
 *         bindVars('This is a string.', '30.66', 23.33, 5, 'dummy');
 * echo "<p>{$db->getSQL()}</p>";
 * // Output: INSERT INTO table VALUES ('This is a string.', 30, 23.33, TRUE, NULL)
 *
 * $db2 = DB::Query('SELECT * FROM table WHERE id = ? LIMIT 1')->bindParams(DB::PARAM_INT)->bindVars(100);
 * echo "<p>{$db2->getSQL()}</p>";
 * // Output: SELECT * FROM table WHERE id = 100 LIMIT 1
 *
 * $db3 = DB::QueryMulti('SELECT * FROM table WHERE last_name = ?')->bindParams(DB::PARAM_STRING)->bindVars('Smith');
 * echo "<p>{$db3->getSQL()}</p>";
 * // Output: SELECT * FROM table WHERE last_name = 'Smith'
 *
 * // Executes the last query
 * $rows = $db3->Execute();
 * </code>
 *
 * @author Patrick Martin <pmartin@mindfireinc.com>
 * @package com_MindFireInc_Database
 * @version 1.0
 */
class DB
{
    const PARAM_STRING = 'string';
    const PARAM_INT = 'int';
    const PARAM_FLOAT = 'float';
    const PARAM_BOOL = 'bool';
    const PARAM_NULL = 'null';
    const PARAM_IGNORE = 'ignore';

    ////////////////////////////////////////////////////////////////////////////
    // STATIC INTERFACE

    /**
     * Name of the server.
     * @var string
     */
    static public $SERVER = 'localhost';
    /**
     * Name of the database.
     * @var string
     */
    //static public $DATABASE = 'applaud';
    static public $DATABASE = 'studioCRM';
    /**
     * Database username.
     * @var string
     */
    //static public $USERNAME = 'root';
    static public $USERNAME = 'dev_studioCRM';
    /**
     * Database password.
     * @var string
     */
    //static public $PASSWORD = 'corvette';
    static public $PASSWORD = 'auto!23$';

    /**
     * Build a query that returns 1 or less rows.
     * @param string $sql sql statement
     * @return DB
     */
    static public function Query($sql)
    {
        return new DB($sql);
    }

    /**
     * Build a query that expects multiple rows.
     * @param string $sql sql statement
     * @return DB
     */
    static public function QueryMulti($sql)
    {
        return new DB($sql, true);
    }

    /**
     * Executes a query.
     * Automatically binds the vars based on type.
     * <code>
     * $rowObject = DB::QueryExecute('SELECT * FROM table WHERE last_name = ? AND active = ? LIMIT 1', 'Smith', true);
     * </code>
     * @param string $sql sql statement
     * @param int|string|bool|float|null $varArgs accepts multiple arguments
     * @return object an object of a single row.
     * @throws an Exception on MySQL errors.
     */
    static public function QueryExecute($sql, $varArgs = null)
    {
        $db = new DB($sql);
        for ($i = 1; $i < func_num_args(); $i++) {
            $var = func_get_arg($i);
            $db->_vars[] = $var;

            if (is_bool($var))
                $db->_params[] = DB::PARAM_BOOL;
            else if (is_null($var))
                $db->_params[] = DB::PARAM_NULL;
            else if (is_float($var))
                $db->_params[] = DB::PARAM_FLOAT;
            else if (is_int($var))
                $db->_params[] = DB::PARAM_INT;
            else //if( is_string($var) )
                $db->_params[] = DB::PARAM_STRING;
        }

        return $db->Execute();
    }

    /**
     * Executes a query that expects multiple rows.
     * Automatically binds the vars based on type.
     * <code>
     * $rows = DB::QueryExecuteMulti('SELECT * FROM table WHERE last_name = ? AND active = ?', 'Smith', true);
     * </code>
     * @param string $sql sql statement
     * @param int|string|bool|float|null $varArgs accepts multiple arguments
     * @return array an array of multiple rows.
     * @throws an Exception on MySQL errors.
     */
    static public function QueryExecuteMulti($sql, $varArgs = null)
    {
        $db = new DB($sql, true);
        for ($i = 1; $i < func_num_args(); $i++) {
            $var = func_get_arg($i);
            $db->_vars[] = $var;

            if (is_bool($var))
                $db->_params[] = DB::PARAM_BOOL;
            else if (is_null($var))
                $db->_params[] = DB::PARAM_NULL;
            else if (is_float($var))
                $db->_params[] = DB::PARAM_FLOAT;
            else if (is_int($var))
                $db->_params[] = DB::PARAM_INT;
            else //if( is_string($var) )
                $db->_params[] = DB::PARAM_STRING;
        }

        return $db->Execute();
    }

    ////////////////////////////////////////////////////////////////////////////
    // PUBLIC INTERFACE

    /**
     * Execute the query and return the results.
     * @return array|object an array if multiple rows, or an object for single row.
     * @throws an Exception on mismatched bindings and MySQL errors.
     */
    public function Execute()
    {
        $result = mysqli_query(self::$_link, $this->getSQL());
        //$result = mysql_query($this->getSQL(), self::$_link);

        if (mysqli_errno(self::$_link))
            throw new Exception(mysqli_error(self::$_link), mysqli_errno(self::$_link));

        $this->_lastInsertId = (int)@mysqli_insert_id(self::$_link);
        $this->_affectedRows = (int)@mysqli_affected_rows(self::$_link);

        if (!$this->_isMulti)
            return @mysqli_fetch_assoc($result);

        $rows = array();
        while ($row = @mysqli_fetch_assoc($result))
            $rows[] = $row;

        @mysqli_free_result($result);

        return $rows;
    }

    /**
     * Bind parameters to the placeholders.
     * @param string $DB_PARAM_CONSTANTS DB::PARAM_* constants
     * @return DB
     */
    public function bindParams($DB_PARAM_CONSTANTS)
    {
        $this->_params = func_get_args();
        return $this;
    }

    /**
     * Bind variables to the placeholders.
     * @param mixed $varArgs values for placeholders
     * @return DB
     */
    public function bindVars($varArgs)
    {
        $this->_vars = func_get_args();
        return $this;
    }

    /**
     * Returns the INSERT id of the last statement, if applicable.
     * @return int insert id of last statement
     */
    public function getLastInsertId()
    {
        return $this->_lastInsertId;
    }

    /**
     * Returns the number of affected rows.
     * @return int number of affected rows
     */
    public function getAffectedRows()
    {
        return $this->_affectedRows;
    }

    /**
     * Returns the SQL statement formed by this instance.
     * @return string sql statement
     * @throws an Exception on mismatched bindings
     */
    public function getSQL()
    {
        if (count($this->_vars) != count($this->_params))
            throw new Exception('Parameters and variable counts do not match');

        if (count($this->_vars) != substr_count($this->_sql, '?'))
            throw new Exception('SQL and variable counts do not match');

        $sqlPieces = explode('?', $this->_sql);
        $sql = $sqlPieces[0];
        for ($i = 0; $i < count($this->_vars); $i++) {
            $var = $this->_vars[$i];
            switch ($this->_params[$i]) {
                case 'int':
                    $var = (int)$var;
                    break;
                case 'float':
                    $var = (float)$var;
                    break;
                case 'bool':
                    $var = (bool)$var ? 'TRUE' : 'FALSE';
                    break;
                case 'null':
                    $var = 'NULL';
                    break;
                case 'ignore':
                    $var = '?';
                    break;
                default: // string
                    $var = '\'' . mysqli_real_escape_string($var, self::$_link) . '\'';
            };
            $sql .= $var . $sqlPieces[$i + 1];
        }
        return $sql;
    }

    ////////////////////////////////////////////////////////////////////////////
    // Private Methods

    static private $_link;
    static private $_db;

    private $_sql;
    private $_isMulti;
    private $_params;
    private $_vars;
    private $_affectedRows;
    private $_lastInsertId;

    private function __construct($sql, $isMulti = false)
    {
        $this->_sql = $sql;
        $this->_isMulti = $isMulti;
        if (self::$_link == null) {
            self::$_link = mysqli_connect(self::$SERVER, self::$USERNAME, self::$PASSWORD, self::$DATABASE);
            /*self::$_link = @mysql_connect(self::$SERVER, self::$USERNAME, self::$PASSWORD);
            if (!self::$_link) {
                throw new Exception('Database Server Connection Error');
            }

            self::$_db = @mysql_select_db(self::$DATABASE, self::$_link);
            if (!self::$_db) {
                throw new Exception('Database Connection Error');
          }*/
        }
    }

}

