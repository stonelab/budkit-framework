<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * results.php
 *
 * Requires PHP version 5.3
 *
 * LICENSE: This source file is subject to version 3.01 of the GNU/GPL License
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.txt  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 * send a note to support@stonyhillshq.com so we can mail you a copy immediately.
 *
 * @category   Library
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database/results
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 */

namespace Budkit\Datastore;

/**
 * What is the purpose of this class, in one sentence?
 *
 * How does this class achieve the desired purpose?
 *
 * @category   Library
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database/results
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
abstract class Results implements Statement
{

    /**
     * A copy of the current database object *RECURSIVE*
     * @var object
     */
    public $dbo;

    /**
     * @var mixed
     */
    public $resultId = NULL;

    /**
     *
     * @var object = NULL;
     */
    public $resultData;

    /**
     *
     * @var interger
     */
    public $currentRow = 0;

    /**
     *
     * @var interger
     */
    public $numrows = 0;

    /**
     *
     * @var connectionId
     */
    public $connectionId = NULL;

    /**
     *
     * @var interger
     */
    public $affectedRows = 0;

    /**
     *
     * @var array
     */
    public $resultObject = array();

    /**
     *
     * @var array
     */
    public $resultArray = array();

    /**
     *
     * @var array
     */
    private $rowData = array();

    /**
     *
     * @var array
     */
    private $_cache = array();

    final public function getQuery()
    {

    }

    final public function getResource()
    {

    }

    final public function getDBO()
    {
        return $this->dbo;
    }

    final public function setDBO($dbo)
    {
        $this->dbo = $dbo;
    }

    public function bindColumn()
    {

    }

    public function bindParam()
    {

    }

    public function bindValue()
    {

    }

    public function closeCursor()
    {

    }

    public function errorCode()
    {

    }

    public function errorInfo()
    {

    }

    /**
     * Returns an array containing all of the result set rows
     *
     * @param type $style , numeric=numeric keys, object=object, array=array
     * @param type $arguments
     */
    public function fetchAll($as = 'array', $arguments = '')
    {

        $rows = array();
        $style = array(
            "array" => "fetchAssoc",
            "numeric" => "fetch",
            "object" => "fetchObject"
        );

        $as = !array_key_exists((string)$as, $style) ? "array" : (string)$as;

        //Getting all as an array
        while ($row = $this->$style[$as]($arguments)) {
            $rows[] = $row;
        }

        //Return the resultset;
        return $rows;
    }

    /**
     * Returns the number of rows affected by the last MySQL query
     *
     * @return type
     */
    final public function getAffectedRows()
    {
        //return parent::getAffectedRows();
        return $this->rowCount();
    }

    /**
     * Alias of setNumRows
     *
     * @param type $n
     * @return Results
     */
    final public function setAffectedRows($n)
    {
        $this->numrows = $n;

        return $this;
    }

    /**
     * Returns the number of rows affected by the last executed statement
     *
     * @return interger
     *
     */
    abstract public function rowCount();

    /**
     * Sets the database connection Id
     *
     * @param type $connectionId
     */
    final public function setConnectionId($connectionId)
    {
        $this->connectionId = $connectionId;

        return $this;
    }

    /**
     * Sets the Result Object
     *
     * @param type $object
     * @return Results
     */
    final public function setResultObject($object)
    {
        $this->resultObject = $object;

        return $this;
    }

    /**
     * Sets the Result Array
     *
     * @param type $array
     * @return Results
     */
    final public function setResultArray($array)
    {

        $this->resultArray = $array;

        return $this;
    }

    /**
     * Sets the number of affected rows
     *
     * @param type $n
     */
    final public function setNumRows($n)
    {
        $this->numrows = $n;

        return $this;
    }

    /**
     * Returns the result Id
     *
     * @return type
     */
    final public function getResultId()
    {
        return $this->resultId;
    }

//    /**
//     * Returns an instance of the statement object
//     *
//     * @staticvar mixed $instance
//     * @param mixed array $options
//     * @return object
//     */
//    public static function getInstance($options = array()) {
//
//        static $instance;
//
//
//        //@TODO check the statement is set in the dbo. before continue
//
//        if (!isset($options) || !is_array($options) || !isset($options['driver'])) {
//            exit('we need to know what driver and have a dbo passed');
//            //@TODO show some form of error; Or attempt to get it from the dbo passed;
//            return false;
//        }
//
//        if (!isset($instance[$options['driver']])) {
//            $instance[$options['driver']] = \call_user_func("Library\Database\Drivers\\" . $options['driver'] . "\Statement::getInstance", $options);
//
//            $instance[$options['driver']]->setDBO($options['dbo']);
//        }
//
//        //If the class was already instantiated, just return it
//        return $instance[$options['driver']];
//    }

    /**
     * Sets the result resource Id
     *
     * @param type $resultId
     */
    final public function setResultId($resultId)
    {
        $this->resultId = $resultId;

        return $this;
    }

    /**
     * Alias of Fectch Assoc;
     *
     * @return type
     */
    public function fetchArray()
    {
        return $this->fetchAssoc();
    }

    /**
     * Returns the next row in the result set as an array
     * With column names (field names) as array Keys
     *
     * @return array
     */
    abstract public function fetchAssoc();

    /**
     * Executes a prepared database sql statement
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    abstract public function execute();

    /**
     * Explains the query used to obetain the results
     *
     * @return
     */
    abstract public function explain();

    /**
     * Frees the resultse
     *
     * @return
     */
    abstract public function freeResults();

    /**
     * Returns metadata for a column in a result set
     *
     * Meta
     *  - name   = the name of the column
     *  - table  = the name of the column table
     *  - length = the length of the column
     *  - flags  = the data flags set for this column
     *
     * @return array assoc array
     */
    abstract public function getColumnMeta();

    /**
     * data seek
     *
     * @return
     */
    abstract public function dataSeek();

    /**
     * Returns the next row in the result set as an object
     * With the column names (fieldnames) as property names
     *
     * @return object;
     */
    abstract public function fetchObject();

    /**
     * Returns the number of columns in the result set represented by the PDOStatement object.
     * If there is no result set, Results::columnCount() returns 0.
     *
     * @return interger the number of columns in the resultset
     */
    abstract public function columnCount();

    /**
     * List all the columns in a result set
     *
     * @return
     */
    abstract public function listColumns();

    /**
     *  Fetches a row in a resultset
     *
     * @return array
     */
    abstract public function fetch();


    /**
     *  Fetches a row in a resultset
     *
     * @return array
     */
    abstract public function lastInsertId();
}