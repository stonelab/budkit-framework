<?php

namespace Budkit\Datastore\Drivers\MySQLi;

use Budkit\Datastore\Driver as DatastoreDriver;
use Budkit\Datastore\Engine;
use Budkit\Datastore\Exception\ConnectionError;
use Budkit\Datastore\Exception\InternalError;
use Budkit\Datastore\Exception\QueryException;
use Budkit\Debug\Log;


final class Driver extends Engine implements DatastoreDriver
{


    var $prefix = "";
    /**
     * The database driver name
     *
     * @var string
     */
    var $name = 'mysqli';
    /**
     *  The null/zero date string
     *
     * @var string
     */
    var $nullDate = '0000-00-00 00:00:00';
    /**
     * Quote for named objects
     *
     * @var string
     */
    var $nameQuote = '`';

    /**
     * Transaction queries
     *
     * @var type
     */
    var $transactions = array();


    var $queries = array();


    public function __construct($options = [])
    {

        $host = array_key_exists('host', $options) ? $options['host'] : 'localhost';
        $user = array_key_exists('user', $options) ? $options['user'] : '';
        $password = array_key_exists('password', $options) ? $options['password'] : '';
        $database = array_key_exists('name', $options) ? $options['name'] : '';
        $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : 'bk_';
        $select = array_key_exists('select', $options) ? $options['select'] : true;


        if (!$this->connect($host, $user, $password, $database, $prefix, $select)) {

            //@TODO throw connection exceptions
            //throw new Exception("Could not connect to database with driver:mysqli");
            ;
            throw new ConnectionError("The requested database driver is not supported");

            return false;
            //throw an exception;
        }

        // Determine utf-8 support
        $this->utf = $this->hasUTF();

        //Set charactersets (needed for MySQL 4.1.2+)
        if ($this->utf) {
            $this->setUTF();
        }

        $this->prefix = $prefix;
        $this->errorNum = 0;
        $this->log = new Log("mysqli-db.log");
        $this->quoted = array();
        $this->hasQuoted = false;
        $this->debug = true;

        // select the database
        if ($select) {
            $this->database($database);
        }
    }


    public function connect($server = 'localhost', $username = '', $password = '', $database = '', $prefix = 'dd_', $select = true)
    {

        if ($this->isConnected()) {
            return true;
        }

        // mysql driver exists?
        if (!function_exists('mysqli_real_connect')) {

            throw new ConnectionError('The MySQLi extension "mysqli" is not available.', 1);

            return false;
        }

        $this->resourceId = mysqli_init();

        if (!$this->resourceId) {
            throw new InternalError('The MySQLi extension "mysqli" initialization failed.', 2);
            return false;
        }

        //We want to keep autocomit on all the time exceplt when we are performing a transaction
        if (!$this->resourceId->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 1')) {
            throw new InternalError('Setting MySQLi to AUTOCOMIT failed.', 3);
            return false;
        }

        if (!$this->resourceId->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
            throw new InternalError('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed.', 4);
            return false;
        }

        // connect to the server
        if (!$this->resourceId->real_connect($server, $username, $password, $database)) {

            throw new ConnectionError(mysqli_connect_error(), mysqli_connect_errno());

            return false;
        }

        // select the database
        if ($select) {
            if (!$this->database($database)) {
                $this->close();
                return false;
            }
        }

        $this->prefix = $prefix;

        return true;

    }


    protected function database($database)
    {
        //Make sure its not empty
        if (!$database) {
            return false;
        }

        //Chooses the database to connect to
        if (!mysqli_select_db($this->resourceId, $database)) {
            throw new ConnectionError('Could not connect to database');
            return false;
        }

        return true;
    }

    /**
     * Reports on the status of the established Object
     *
     * @return boolean
     */
    public function isConnected()
    {

        if (is_a($this->resourceId, "mysqli")) {
            return mysqli_ping($this->resourceId);
        }
        return false;
    }


    public function getVersion()
    {
        return mysqli_get_server_info($this->resourceId);
    }


    /**
     * Database object destructor
     *
     * @return boolean
     */
    final public function __destruct()
    {
    }


    /**
     * Returns a Datastore\Table representation of Table
     *
     * @param $tablename
     */
    final public function getTable($tablename)
    {

        return new Table($tablename, $this);

    }


    final public function close()
    {
        $return = false;
        if (is_a($this->resourceId, "mysqli")) {
            $return = mysqli_close($this->resourceId);
        }
        $this->resourceId = NULL;
        return $return;
    }

    /**
     * Test to see if the MySQL connector is available
     *
     * @static
     * @access public
     * @return boolean  True on success, false otherwise.
     */
    final public function test()
    {
        return (function_exists('mysqli_connect'));
    }

    /**
     * Determines if the connection to the server is active.
     *
     * @access    public
     * @return    boolean
     */
    final public function connected()
    {
        if (is_a($this->resourceId, "mysqli")) {
            return mysqli_ping($this->resourceId);
        }
        return false;
    }

    /**
     * Determines UTF support
     *
     * @access    public
     * @return boolean True - UTF is supported
     */
    final public function hasUTF()
    {
        $verParts = explode('.', $this->getVersion());
        return ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int)$verParts[2] >= 2));
    }

    /**
     * Custom settings for UTF support
     *
     * @access    public
     */
    final public function setUTF()
    {
        mysqli_query($this->resourceId, "SET NAMES 'utf8'");
    }

    /**
     * Get a database escaped string
     *
     * @param    string    The string to be escaped
     * @param    boolean    Optional parameter to provide extra escaping
     * @return    string
     * @access    public
     * @abstract
     */
    final public function getEscaped($text, $extra = false)
    {
        $result = mysqli_real_escape_string($this->resourceId, $text);
        if ($extra) {
            $result = addcslashes($result, '%_');
        }
        return $result;
    }


    final public function exec($query = '')
    {

        //@TODO how to verify the resource Id
        if (!is_a($this->resourceId, "mysqli")) {
            throw new QueryException(t("No valid connection resource found"));
            return false;
        }

        // Take a local copy so that we don't modify the original query and cause issues later
        $sql = (empty($query)) ? $this->query : $query;
        $this->query = $sql = $this->replacePrefix($sql); //just for reference

        if ($this->limit > 0 || $this->offset > 0) {
            $sql .= ' LIMIT ' . max($this->offset, 0) . ', ' . max($this->limit, 0);
        }

        if ($this->debug) {
            $this->ticker++;
            $this->queries[] = $sql;
            $log = htmlentities($sql);

            //Does not play nice with the parser;
            $this->log->message("DB Query {$this->ticker}:\n".$log);
        }

        $this->cursor = mysqli_query($this->resourceId, $sql);

        if (!$this->cursor) {
            throw new QueryException( mysqli_error($this->resourceId) . " SQL=$sql");

            return false;
        }

        $this->resetRun();

        //echo $this->cursor;

        return $this->cursor;
    }


    final public function prepare($statement = NULL, $offset = 0, $limit = 0, $prefix = '')
    {

        //Sets the query to be executed;

        $this->offset = (int)$offset;
        $this->limit = (int)$limit;
        $this->prefix = (!isset($prefix) && !empty($prefix)) ? $prefix : $this->prefix;
        $this->query = $this->replacePrefix($statement);

        //Get the Result Statement class;

        return new Statement($this);
    }

    /**
     * Begins a database transaction
     *
     * @return void;
     */
    public function startTransaction()
    {

        if (!is_a($this->resourceId, "mysqli")) {
            throw new QueryException("No valid db resource Id found. This is required to start a transaction");
            return false;
        }
        $this->resourceId->autocommit(FALSE); //Turns autocommit off;

    }

    /**
     * This method is intended for use in transsactions
     *
     * @param type $sql
     * @param type $execute
     * @return boolean
     */
    public function query($sql, $execute = FALSE)
    {

        $query = (empty($sql)) ? $this->query : $sql;
        $this->transactions[] = $this->replacePrefix($query); //just for reference

        //@TODO;
        if ($execute) {
            $this->resetRun();
            $this->exec($query);
        }

        return true;
    }

    /**
     * Commits a transaction or rollbacks on error
     *
     * @return boolean
     */
    public function commitTransaction()
    {

        if (empty($this->transactions) || !is_array($this->transactions)) {
            throw new QueryException(t("No transaction queries found"));
            $this->transactions = array();


            $this->resourceId->autocommit(TRUE); //Turns autocommit back on
            return false;
        }
        //Query transactions
        foreach ($this->transactions as $query) {


            if (!$this->exec($query)) {

                $this->resourceId->rollback(); //Rolls back the transaction;
                $this->transactions = array();
                $this->resourceId->autocommit(TRUE); //Turns autocommit back on


                return false;
            }
        }

        //Commit the transaction
        if (!$this->resourceId->commit()) {
            throw new QueryException(t("The transaction could not be committed"));
            $this->transactions = array();
            $this->resourceId->autocommit(TRUE); //Turns autocommit back on
            return false;
        }

        $this->transactions = array();
        $this->resourceId->autocommit(TRUE); //Turns autocommit back on
        return true;
    }


    /**
     * For active record querying ONLY
     *
     * @param string $method
     * @param mixed $args
     * @return mixed
     */
    final public function __call($method, $args)
    {

        $activeRecord = new Accessory($this);


        if (!\method_exists($activeRecord, $method)) {
            throwException(t("Database Method {$method} does not exists"));
            return false;
        }

        //Call the Method;
        return @\call_user_func_array(array($activeRecord, $method), $args);
    }
}