<?php

namespace Budkti\Datastore;


/**
 * Database abstraction handler
 *
 * This Database class is a database independent query interface definition.
 * It allows you to connect to different data sources like MySQL, SQLite and
 * other RDBMS on a Win32 operating system. Moreover the possibility exists to
 * use MS Excel spreadsheets, XML, text files and other not relational data
 * as data source.
 *
 * @category   Library
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 * @uses        Library\Database\Activerecord For magical Query building
 * @uses        Library\Database\Table For handling tablesets in the DB
 * @uses        Library\Database\Results For handling query resultsets
 * @uses        Library\Database\Drivers\MySQL\Driver For a MySQL abstraction;
 * @uses        Library\Database\Drivers\MySQLi\Driver For MySQLi;
 * @uses        Library\Database\Drivers\SQLite3\Driver For SQLite3;
 * @uses        Library\Database\Drivers\PostgreSQL\Driver For PostgreSQL;

 */
abstract class Database{

    /**
     * The database connection resource id
     * @var resource
     */
    public $resourceId;

    /**
     * The current driver being used
     * @var string
     */
    public $driver;

    /**
     * The last query to be executed in this connection
     *
     * @var string
     */
    public $query;

    /**
     * Offset Value
     *
     * @var interger
     */
    public $offset;

    /**
     * A limit for the resultset
     *
     * @var interger
     */
    public $limit;


    /**
     * Counts the number of queries executed by exec
     *
     * @var type
     */
    var $ticker;


    /**
     * Method to check that we are in transaction mode
     *
     * @var boolean
     */
    public $tMode = false;

    /**
     * Database debug mode
     *
     * @var boolean
     */
    public $debug = false;


    /**
     * Returns the datbase connection resource ID
     *
     * @return bool FALSE if not connected / ID if found
     */
    final public function getResourceId() {
        return $this->resourceId;
    }

    /**
     * Prepares an SQL query for execution;
     *
     * @param string $statement
     * @return object \Library\Database\Results
     */
    final public function prepare($statement = NULL, $offset = 0, $limit = 0, $prefix='') {

        //Sets the query to be executed;

        $cfg = Config::getParamSection('database');


        $this->offset = (int) $offset;
        $this->limit = (int) $limit;
        $this->prefix = (!isset($prefix) && !empty($prefix)) ? $prefix : $cfg['prefix'];
        $this->query = $this->replacePrefix($statement);

        //Get the Result Statement class;
        $options = array(
            "dbo" => $this,
            "driver" => $this->driver
        );

        $RESULT = \Library\Database\Results::getInstance($options);

        //$this->resetRun();
        //Driver Statements, handle the execute
        return $RESULT;
    }

    /**
     * Returns the current driver object
     *
     * @return Object
     */
    final public function getDriver() {
        return $this->driver;
    }

    /**
     * Returns the total number of Queries executed thus far
     *
     * @return interger
     */
    final public function getTotalQueryCount() {
        return $this->ticker;
    }

    /**
     * Returns a log of total number of Queries executed thus far
     *
     * @return array
     */
    final public function getQueryLog() {
        return $this->log;
    }

    /**
     * For active record querying ONLY
     *
     * @param string $method
     * @param mixed $args
     * @return mixed
     */
    final public function __call($method, $args) {

        //Get the AR class;
        $options = array(
            "dbo" => $this,
            "driver" => $this->driver
        );
        $AR = \Library\Database\ActiveRecord::getInstance($options);


        if (!\method_exists($AR, $method)) {
            $this->setError(_t('Method does not exists'));
            return false;
        }

        //Call the Method;
        return @\call_user_func_array(array($AR, $method), $args);
    }

    /**
     * Returns an instance of the database object for the active driver
     *
     * @staticvar self $instance
     * @param array $options
     * @return object Database
     */
    public static function getInstance($options = array(), $reinstantiate = FALSE) {

        static $instances = array();

        //If the database has not yet been installed return false;
        if (!isset($instances)) {
            $instances = array();
        }

        $dbparams = Config::getParamSection("database");

        if (!isset($dbparams) OR count($dbparams) == 0) {
            //display some sort of error;
            static::setError(_t('DB params not set or not properly formatted'));
            return false;
        }

        if (!isset($dbparams['driver'])) {
            //die;
            //we can't work without this
            static::setError(_t('We need to know what driver your using'));
            return false;
        }

        //serialize
        $signature  = md5(serialize($dbparams));
        $driver     = $dbparams['driver'] = preg_replace('/[^A-Z0-9_\.-]/i', '', $dbparams['driver']);

        if(!class_exists("Library\Database\Drivers\\" . $driver . "\Driver")){
            return false;
        }

        if (!isset($instances[$signature]) || $reinstantiate):
            $instances[$signature] = \call_user_func("Library\Database\Drivers\\" . $driver . "\Driver::getInstance", $dbparams);
        endif;


        if (!\is_object($instances[$signature])) {
            static::setError(sprintf( _t('Could not instantiate database object for the driver: %s'), $driver) );
            return false;
        }

        $instances[$signature]->driver = $driver;

        return $instances[$signature];
    }

    /**
     * Quotes a string in a query,
     *
     * @param string $text
     * @param boolean $escaped
     * @return string quoted string
     */
    public function quote($text, $escaped = true) {
        return '\'' . ($escaped ? $this->getEscaped($text) : $text) . '\'';
    }

    /**
     * This function replaces a string identifier <var>$prefix</var> with the
     * string held is the <var>_table_prefix</var> class variable.
     *
     * @access public
     * @param string The SQL query
     * @param string The common table prefix
     * @return void
     */
    final public function replacePrefix($sql, $prefix='?') {

        $sql = trim($sql);

        $escaped = false;
        $quoteChar = '';

        $n = strlen($sql);

        $startPos = 0;
        $literal = '';
        while ($startPos < $n) {
            $ip = strpos($sql, $prefix, $startPos);

            if ($ip === false) {
                break;
            }
            $j = strpos($sql, "'", $startPos);
            $k = strpos($sql, '"', $startPos);

            if (($k !== FALSE) && (($k < $j) || ($j === FALSE))) {
                $quoteChar = '"';
                $j = $k;
            } else {
                $quoteChar = "'";
            }

            if ($j === false) {
                $j = $n; //the length of the sting
            }

            $literal .= str_replace($prefix, $this->prefix, substr($sql, $startPos, $j - $startPos));
            $startPos = $j;

            $j = $startPos + 1;

            if ($j >= $n) {
                break;
            }

            // THe last bit of the statement
            // quote comes first, find end of quote
            while (TRUE) {
                $k = strpos($sql, $quoteChar, $j);
                $escaped = false;
                if ($k === false) {
                    break;
                }
                $l = $k - 1;
                while ($l >= 0 && $sql{$l} == '\\') {
                    $l--;
                    $escaped = !$escaped;
                }
                if ($escaped) {
                    $j = $k + 1;
                    continue;
                }
                break;
            }
            if ($k === FALSE) {
                // error in the query - no end quote; ignore it
                break;
            }
            $literal .= substr($sql, $startPos, $k - $startPos + 1);
            $startPos = $k + 1;
        }
        if ($startPos < $n) {
            $literal .= substr($sql, $startPos, $n - $startPos);
        }
        return $literal;
    }

    /**
     * Object destructor, must be declared in all drivers
     *
     * @return void
     */
    abstract public function __destruct();

    /**
     * Closes the database connection
     *
     * @return void
     */
    abstract public function close();

    /**
     * Determine if the database object is connected to a DBMS
     *
     * @return void
     */
    abstract public function isConnected();

    /**
     * Custom driver text escaping, Must be defined in the driver
     *
     * @return void
     */
    abstract public function getEscaped($text, $extra = false);

    /**
     * Gets the version of the currrent DBMS driver
     *
     * @return void
     */
    abstract public function getVersion();


    /**
     * Driver connect method
     * @return boolean
     */
    abstract public function connect($server = 'localhost', $username = '', $password = '', $dbname = '');

    /**
     * Custom Tests, For connectivity test, use Database::isConnected
     *
     * @return void
     */
    abstract public function test();

    /**
     * Alias of Database isConected method
     *
     * @return void
     */
    abstract public function connected();

    /**
     * Determines if the database driver, has UTF handling capabilities
     * And returns its default settings
     *
     * @return void
     */
    abstract public function hasUTF();

    /**
     * Sets the UTF Charset type
     *
     * @return void
     */
    abstract public function setUTF();

    /**
     * Executes a predifined query
     *
     * @return void
     */
    abstract public function exec($query='');

    /**
     * Begins a database transaction
     *
     * @return void;
     */
    abstract public function startTransaction();


    /**
     * This method is intended for use in transsactions
     *
     * @return boolean
     */
    abstract public function query($sql, $execute =FALSE);

    /**
     * Commits a transaction or rollbacks on error
     *
     * @return boolean
     */
    abstract public function commitTransaction();


}