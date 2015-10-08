<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 12/09/15
 * Time: 19:13
 */

namespace Budkit\Datastore;

abstract class Engine{

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
}