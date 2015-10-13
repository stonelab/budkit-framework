<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 12/09/15
 * Time: 19:07
 */

namespace Budkit\Datastore;


interface Driver
{

    /**
     * Object destructor, must be declared in all drivers
     *
     * @return void
     */
    public function __destruct();

    /**
     * Closes the database connection
     *
     * @return void
     */
    public function close();

    /**
     * Determine if the database object is connected to a DBMS
     *
     * @return void
     */
    public function isConnected();

    /**
     * Custom driver text escaping, Must be defined in the driver
     *
     * @return void
     */
    public function getEscaped($text, $extra = false);

    /**
     * Gets the version of the currrent DBMS driver
     *
     * @return void
     */
    public function getVersion();


    /**
     * Driver connect method
     * @return boolean
     */
    public function connect($server = 'localhost', $username = '', $password = '', $dbname = '');

    /**
     * Custom Tests, For connectivity test, use Database::isConnected
     *
     * @return void
     */
    public function test();

    /**
     * Alias of Database isConected method
     *
     * @return void
     */
    public function connected();

    /**
     * Determines if the database driver, has UTF handling capabilities
     * And returns its default settings
     *
     * @return void
     */
    public function hasUTF();

    /**
     * Sets the UTF Charset type
     *
     * @return void
     */
    public function setUTF();

    /**
     * Executes a predifined query
     *
     * @return void
     */
    public function exec($query = '');

    /**
     * Begins a database transaction
     *
     * @return void;
     */
    public function startTransaction();


    /**
     * This method is intended for use in transsactions
     *
     * @return boolean
     */
    public function query($sql, $execute = FALSE);

    /**
     * Commits a transaction or rollbacks on error
     *
     * @return boolean
     */
    public function commitTransaction();


    //ENGINE TRAIT METHODS

    /**
     * Returns the datbase connection resource ID
     *
     * @return bool FALSE if not connected / ID if found
     */
    public function getResourceId();

    /**
     * Prepares an SQL query for execution;
     *
     * @param string $statement
     * @return object \Library\Database\Results
     */
    public function prepare($statement = NULL, $offset = 0, $limit = 0, $prefix = '');

    /**
     * Returns the current driver object
     *
     * @return Object
     */
    public function getDriver();

    /**
     * Returns the total number of Queries executed thus far
     *
     * @return interger
     */
    public function getTotalQueryCount();

    /**
     * Returns a log of total number of Queries executed thus far
     *
     * @return array
     */
    public function getQueryLog();

    /**
     * For active record querying ONLY
     *
     * @param string $method
     * @param mixed $args
     * @return mixed
     */
    public function __call($method, $args);

    /**
     * Quotes a string in a query,
     *
     * @param string $text
     * @param boolean $escaped
     * @return string quoted string
     */
    public function quote($text, $escaped = true);

    /**
     * This function replaces a string identifier <var>$prefix</var> with the
     * string held is the <var>_table_prefix</var> class variable.
     *
     * @access public
     * @param string The SQL query
     * @param string The common table prefix
     * @return void
     */
    public function replacePrefix($sql, $prefix = '?');

    /**
     * Returns a Datastore\Table object of table
     *
     * @param $tablename
     * @return mixed
     */
    public function getTable($tablename);

}