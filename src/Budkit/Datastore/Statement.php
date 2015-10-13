<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 12/09/15
 * Time: 19:42
 */

namespace Budkit\Datastore;

interface Statement
{

    public function getQuery();

    public function getResource();

    public function setDBO($dbo);

    public function getDBO();

    public function bindColumn();

    public function bindParam();

    public function bindValue();

    public function closeCursor();

    public function errorCode();

    public function errorInfo();

    /**
     * Returns an array containing all of the result set rows
     *
     * @param type $style , numeric=numeric keys, object=object, array=array
     * @param type $arguments
     */
    public function fetchAll($as = 'array', $arguments = '');

    /**
     * Returns the number of rows affected by the last MySQL query
     *
     * @return type
     */
    public function getAffectedRows();

    /**
     * Sets the result resource Id
     *
     * @param type $resultId
     */
    public function setResultId($resultId);

    /**
     * Sets the database connection Id
     *
     * @param type $connectionId
     */
    public function setConnectionId($connectionId);

    /**
     * Sets the Result Object
     *
     * @param type $object
     * @return Results
     */
    public function setResultObject($object);

    /**
     * Sets the Result Array
     *
     * @param type $array
     * @return Results
     */
    public function setResultArray($array);

    /**
     * Sets the number of affected rows
     *
     * @param type $n
     */
    public function setNumRows($n);

    /**
     * Alias of setNumRows
     *
     * @param type $n
     * @return Results
     */
    public function setAffectedRows($n);

    /**
     * Returns the result Id
     *
     * @return type
     */
    public function getResultId();


    /**
     * Alias of Fectch Assoc;
     *
     * @return type
     */
    public function fetchArray();

    /**
     * Executes a prepared database sql statement
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function execute();

    /**
     * Explains the query used to obetain the results
     *
     * @return
     */
    public function explain();

    /**
     * Frees the resultse
     *
     * @return
     */
    public function freeResults();

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
    public function getColumnMeta();

    /**
     * data seek
     *
     * @return
     */
    public function dataSeek();

    /**
     * Returns the next row in the result set as an object
     * With the column names (fieldnames) as property names
     *
     * @return object;
     */
    public function fetchObject();

    /**
     * Returns the next row in the result set as an array
     * With column names (field names) as array Keys
     *
     * @return array
     */
    public function fetchAssoc();

    /**
     * Returns the number of columns in the result set represented by the PDOStatement object.
     * If there is no result set, Results::columnCount() returns 0.
     *
     * @return interger the number of columns in the resultset
     */
    public function columnCount();

    /**
     * List all the columns in a result set
     *
     * @return
     */
    public function listColumns();

    /**
     * Returns the number of rows affected by the last executed statement
     *
     * @return interger
     *
     */
    public function rowCount();

    /**
     *  Fetches a row in a resultset
     *
     * @return array
     */
    public function fetch();


    /**
     *  Fetches a row in a resultset
     *
     * @return array
     */
    public function lastInsertId();

}