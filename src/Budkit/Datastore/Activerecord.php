<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * activerecord.php
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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database/activerecord
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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database/activerecord
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
abstract class Activerecord
{

    /**
     *
     * @var boolean
     */
    var $arrayAll = FALSE;
    /**
     *
     * @var array
     */
    var $arraySelect = array();
    /**
     *
     * @var boolean
     */
    var $arrayDistinct = FALSE;
    /**
     *
     * @var array
     */
    var $arrayFrom = array();
    /**
     *
     * @var array
     */
    var $arrayJoin = array();
    /**
     *
     * @var array
     */
    var $arrayWhere = array();
    /**
     * Adds a subquery to the Table to exclude
     * results found in other tables
     * @var array
     */
    var $arrayWhereNotExists = array();
    /**
     *
     * @var like
     */
    var $arrayLike = array();
    /**
     *
     * @var array
     */
    var $arrayGroupBy = array();
    /**
     *
     * @var array
     */
    var $arrayHaving = array();
    /**
     *
     * @var boolean
     */
    var $arrayLimit = FALSE;
    /**
     *
     * @var boolean
     */
    var $arrayOffset = FALSE;
    /**
     *
     * @var boolean
     */
    var $arrayOrder = FALSE;
    /**
     *
     * @var array
     */
    var $arrayOrderBy = array();
    /**
     *
     * @var array
     */
    var $arrayWhereIn = array();
    /**
     *
     * @var array
     */
    var $arrayAliasedTables = array();
    /**
     *
     * @var array
     */
    var $arrayStoreArray = array();
    /**
     *
     * @var array
     */
    var $arrayUnion = array();
    /**
     *
     * @var boolean
     */
    var $cachingOn = FALSE;
    /**
     *
     * @var boolean
     */
    var $arrayCaching = FALSE;
    /**
     *
     * @var array
     */
    var $arrayCacheExists = array();
    /**
     *
     * @var array
     */
    var $arrayCacheSelect = array();
    /**
     *
     * @var array
     */
    var $arrayCacheFrom = array();
    /**
     *
     * @var array
     */
    var $arrayCacheJoin = array();
    /**
     *
     * @var array
     */
    var $arrayCacheWhere = array();
    /**
     *
     * @var array
     */
    var $arrayCacheLike = array();
    /**
     *
     * @var array
     */
    var $arrayCacheGroupby = array();
    /**
     *
     * @var array
     */
    var $arrayCacheHaving = array();
    /**
     *
     * @var array
     */
    var $arrayCacheOrderBy = array();
    /**
     *
     * @var array
     */
    var $arrayCacheSet = array();
    /**
     * Specifies the chache directory to use
     *
     * @var string Path to cache directory. global defined is TUIYO_CACHE
     */
    var $cachedir = '';
    /**
     * The database object
     * @var object Database
     */
    protected $DBO;
    /**
     * The syntax to count rows is slightly different across different
     * database engines, so this string appears in each driver and is
     * used for the count_all() and count_all_results() functions.
     *
     * @var string SQL statement
     */
    private $_countString = 'SELECT COUNT(*) AS ';
    /**
     * Generates a database specific random keyword
     *
     * @var string RANDOM query statement per driver
     */
    private $_randomKeyword = ' RAND()';
    /**
     *
     * @var object TuiyoDatabaseCache
     */
    private $CACHE;
    /**
     *
     * @var object TuiyoDatabaseResult
     */
    private $RESULT;

    /**
     * Specifies the table upon which a read or write query is performed
     *
     * @param string $table the table name with any prefix. e.g #__users
     * @param boolean $getData if set to true, this method returns all stored data array
     * @param mixed $dataType
     * @return object TuiyoDatabase
     *
     */
    final public function table($table, $getData = false, $dataType = null)
    {
        if (!empty($table)) {
            $this->from($table);

            //if getData; run getData;
        }
        return $this;
    }

    /**
     * Specifies the table from which we are reading. {@method table}
     * @param string $table The table name
     * @return object TuiyoDatabase
     */
    final public function from($table)
    {

        $table = (is_array($table)) ? (array)$table : (strpos($table, ',') !== FALSE) ? explode(',', $table) : array($table);

        foreach ($table as $source) {

            if (empty($source))
                continue;

            //First check for the presence of any aliasis
            $source = trim($source);
            $this->traceTableAlias($source);

            $this->arrayFrom[] = $this->identifiers($source);

            if ($this->arrayCaching) {
                $this->arrayCacheFrom[] = $this->identifiers($source);
                $this->arrayCacheExists[] = 'from';
            }
        }
        return $this;
    }

    /**
     * This method allows us to preserve and track table aliasis
     * as we build the query statement
     *
     * @access private
     * @param string $table the table name
     * @return void
     */
    final public function traceTableAlias($table)
    {

        if (is_array($table)) {
            foreach ($table as $t) {
                $this->traceTableAlias($t);
            }
            return;
        }
        // Does the string contain a comma?  If so, we need to separate
        // the string into discreet statements
        if (strpos($table, ',') !== FALSE) {
            return $this->traceTableAlias(explode(',', $table));
        }
        // if a table alias is used we can recognize it by a space
        if (strpos($table, " ") !== FALSE) {
            // if the alias is written with the AS keyword, remove it
            $table = preg_replace('/ AS /i', ' ', $table);

            // Grab the alias
            $table = trim(strrchr($table, " "));

            // Store the alias, if it doesn't already exist
            if (!in_array($table, $this->arrayAliasedTables)) {
                $this->arrayAliasedTables[] = $table;
            }
        }
    }

    /**
     * Helper method for modifying any indentifiers in the query
     *
     * @param string $identifier
     * @access private
     * @return string modified identifier e.g escaped, santized etc
     *
     */
    final public function identifiers($identifier)
    {
        // Convert tabs or multiple spaces into single spaces
        $identifier = preg_replace('/[\t ]+/', ' ', $identifier);

        // If the item has an alias declaration we remove it and set it aside.
        // Basically we remove everything to the right of the first space
        $alias = '';
        if (strpos($identifier, ' ') !== FALSE) {
            $alias = strstr($identifier, " ");
            $identifier = substr($identifier, 0, -strlen($alias));
        }

        if (strpos($identifier, '.') !== FALSE) {

            //do something
        }

        //return for now
        return $identifier . $alias;
    }

    /**
     * Adds the ORDER BY clause to the query statement
     *
     * @param string $orderby
     * @param string $direction
     * @return ActiveRecord
     */
    final public function orderBy($orderby, $direction = '')
    {
        if (strtolower($direction) == 'random') {
            $orderby = ''; // Random results want or don't need a field name
            $direction = $this->_randomKeyword;
        } elseif (trim($direction) != '') {
            $direction = (in_array(strtoupper(trim($direction)), array('ASC', 'DESC'), TRUE)) ? ' ' . $direction : ' ASC';
        }


        if (strpos($orderby, ',') !== FALSE) {
            $temp = array();
            foreach (explode(',', $orderby) as $part) {
                $part = trim($part);
                if (!in_array($part, $this->arrayAliasedTables)) {
                    $part = $this->identifiers(trim($part));
                }
                $temp[] = $part;
            }

            $orderby = implode(', ', $temp);
        } else if ($direction != $this->_randomKeyword) {
            $orderby = $this->identifiers($orderby);
        }

        $orderbyStatement = $orderby . $direction;

        $this->arrayOrderBy[] = $orderbyStatement;
        if ($this->arrayCaching === TRUE) {
            $this->arrayCacheOrderBy[] = $orderbyStatement;
            $this->arrayCacheExists[] = 'orderby';
        }

        return $this;
    }

    /**
     * Specifies the fields to be selected from the database.
     *
     * @param mixed $fields . Could be an array or a string of fields, in the table
     * @return ActiveRecord
     */
    final public function select($fields = '*')
    {


        if (!is_array($fields)) {
            if (strpos($fields, ',') !== FALSE) {
                $fields = explode(',', $fields);
            } else {
                $fields = array($fields);
            }
        }

        foreach ($fields as $field) {
            $field = trim($field);
            if (!is_int($field) && empty($field)) {
                continue; //if empty and not zero?
            }

            $this->arraySelect[] = $field;

            if ($this->arrayCaching) {
                $this->arrayCacheSelect[] = $field;
                $this->arrayCacheExists[] = 'select';
            }
        }
        return $this;
    }

    /**
     * Counts ALL the records in the table
     *
     * @param string $table the tablename
     * @return interger Record Count
     */
    final public function countAll($table = '')
    {
        return $this->count($table);
    }

    /**
     * Counts all the records in the specified table
     * Or records which satisfy the compiled SQL statement
     *
     * @param string $table
     * @return interger
     */
    final public function count($table = '', $resetselect = true)
    {

        if (!empty($table)) {
            $this->traceTableAlias($table);
            $this->fromTable($table);
        }
        $sql = $this->compile($this->_countString . $this->identifiers('totalcount'), true);
        $statement = $this->DBO->prepare($sql);

        $results = $statement->execute();

        if ($resetselect) $this->resetSelect();
        if ($results->rowCount() == 0) return '0';

        $row = $results->fetchObject();

        return $row->totalcount;
    }

    /**
     * Compiles into a single query statement
     *
     * @return string
     */
    final public function compile($sql = '', $counting = false)
    {
        $query = '';

        if (!empty($sql)) {
            $query .= $sql; //escape, replace prefixxes and all that!
        } else {
            //select [all|distinct]
            $select = (is_numeric($this->arrayLimit)) ? 'SELECT SQL_CALC_FOUND_ROWS ' : 'SELECT';
            $query .= ($this->arrayDistinct) ? $select . ' DISTINCT ' : ($this->arrayAll || count($this->arraySelect) < 1) ? $select . ' ALL ' : $select . ' ';

            //Count
            //expression {, expression}
            foreach ($this->arraySelect as $key => $field) {
                $this->arraySelect[$key] = $this->identifiers($field);
            }
            $query .= implode(', ', $this->arraySelect);
        }
        //from tablename [corr_name] {, tablename [corr_name]}
        if (!empty($this->arrayFrom)) {
            $query .= "\nFROM\t";
            $tables = $this->arrayFrom;
            if (!is_array($this->arrayFrom)) {
                $tables = array($this->arrayFrom);
            }
            $query .= '(' . implode(', ', $tables) . ')';
        }

        //joins;
        if (!empty($this->arrayJoin) && !$counting) {
            $query .= "\n";
            $query .= implode("\n", $this->arrayJoin);
        }
        //[where search_condition]
        //@TODO whereIn, Like, whereNotIn etc...

        if (!empty($this->arrayWhere)) {
            $query .= "\n";
            $query .= "WHERE\t";
        }

        $query .= implode("\n", $this->arrayWhere);
        // LIKE
        if (!empty($this->arrayLike)) {
            if (!empty($this->arrayWhere)) {
                $query .= "\nAND\t";
            }
            $query .= implode("\n\t", $this->arrayLike);
        }

        //[group by column {, column}]

        if (count($this->arrayGroupBy) > 0 && !$counting) {

            $query .= "\nGROUP BY\t";
            $query .= implode(', ', $this->arrayGroupBy);
        }

        //[having search_condition]
        if (!empty($this->arrayHaving)) {
            $query .= "\nHAVING\t";
            $query .= implode("\n", $this->arrayHaving);
        }

        //{union [all] Subselect}
        if (!empty($this->arrayUnion)) {
            //@TODO recompile subelects and add to unions?
        }

        //[order by result_column [asc|desc]
        if (!empty($this->arrayOrderBy)) {
            $query .= "\nORDER BY\t";
            $query .= implode(', ', $this->arrayOrderBy);

            if ($this->arrayOrder !== FALSE) {
                $query .= ($this->arrayOrder == 'desc') ? ' DESC' : ' ASC';
            }
        }

        // ----------------------------------------------------------------
        // Write the "LIMIT" portion of the query

        if (is_numeric($this->arrayLimit)) {
            $query .= "\n";
            $offset = $this->arrayOffset;
            $limit = $this->arrayLimit;

            if ($offset == 0) {
                $offset = '';
            } else {
                $offset .= ", ";
            }
            $query .= "LIMIT\t" . $offset . $limit;
        }
        $this->resetSelect();

        return $query;
    }

    final public function resetSelect()
    {

        $resetItemsarray = array(
            'arraySelect' => array(),
            'arrayFrom' => array(),
            'arrayJoin' => array(),
            'arrayWhere' => array(),
            'arrayLike' => array(),
            'arrayGroupBy' => array(),
            'arrayHaving' => array(),
            'arrayOrderBy' => array(),
            'arrayWhereIn' => array(),
            'arrayAliasedTables' => array(),
            'arrayDistinct' => FALSE,
            'arrayLimit' => FALSE,
            'arrayOffset' => FALSE,
            'arrayOrder' => FALSE,
            'arraySet' => array()
        );

        $this->resetRun($resetItemsarray);
    }

    final public function resetRun($resetItemsarray = NULL)
    {

        if (!is_array($resetItemsarray)) {
            $this->resetSelect();
            $this->resetWrite();
            return TRUE;
        }
        foreach ($resetItemsarray as $item => $default) {
            if (!in_array($item, $this->arrayStoreArray)) {
                $this->$item = $default;
            }
        }
    }

    final public function resetWrite()
    {

        $resetItemsarray = array(
            'arraySet' => array(),
            'arrayFrom' => array(),
            'arrayWhere' => array(),
            'arrayLike' => array(),
            'arrayOrderBy' => array(),
            'arrayLimit' => FALSE,
            'arrayOrder' => FALSE
        );

        $this->resetRun($resetItemsarray);
    }

    /**
     * NOTE: As of now you cannot use aliasing in a Max Clause
     * @fields
     * @return TuiyoDatabase
     *
     */
    final public function max($field)
    {

        if (!is_array($fields)) {
            if (strpos($fields, ',') !== FALSE) {
                $fields = explode(',', $fields);
            } else {
                $fields = array($fields);
            }
        }

        foreach ($fields as $field) {
            $field = trim($field);
            if (!is_int($field) && empty($field)) {
                continue; //if empty and not zero?
            }
            $this->arraySelect[] = $field;

            if ($this->arrayCaching) {
                $this->arrayCacheSelect[] = $field;
                $this->arrayCacheExists[] = 'select';
            }
        }

        return $this;
    }

    /**
     *
     * @return TuiyoDatabase
     */
    final public function min()
    {
        return $this;
    }

    /**
     *
     * @return TuiyoDatabase
     */
    final public function sum()
    {
        return $this;
    }

    /**
     *
     * @return TuiyoDatabase
     */
    final public function average()
    {
        return $this;
    }

    /**
     * Adds an OR WHERE condition the the compiled Statement
     * See {@method where}
     *
     * @param string $key
     * @param mixed $values
     * @param string $type
     * @return object TuiyoDatabase
     */
    final public function orWhere($key, $values = NULL, $type = 'OR')
    {
        return $this->where($key, $values, $type);
    }

    /**
     * Adds a WHERE condition to the Query statement.
     * NOTE, to change the operatory, append the desired operator to the key names
     * for example use
     *
     * where('a >', 'b'); to add an a > b rule to the statement
     * where( array('a >'=>'b','a <'='c') );
     *
     * @param mixed $key string with a single field key or array of key value pairs
     * @param string $value If $key is string, then a sing $single String to specify the value
     * @param string $type if adding more than one WHERE clause, use AND, OR etc..
     * @param boolean $escape . Should we escape the identifiers in the statement?
     * @return TuiyoDatabase
     */
    final public function where($key, $value = NULL, $type = 'AND', $escape = TRUE)
    {

        if (empty($key)) {
            return $this;
        }
        if (!is_array($key)) {
            if (is_null($value)) { //some values could be ''
                return $this;
            }
            $key = array($key => $value);
        }

        //print_R($key);

        foreach ($key as $k => $v) {

            //The firs item adds the and prefix;
            $prefix = (count($this->arrayWhere) == 0 AND count($this->arrayCacheWhere) == 0) ? '' : $type . "\t";

            if (is_null($v) && !$this->hasOperator($k)) {
                // value appears not to have been set, assign the test to IS NULL
                $k .= ' IS NULL';
            }
            if (!is_null($v)) {
                if ($escape === TRUE) {
                    $k = $this->identifiers($k);
                    $v = '' . $this->escape($v);
                    //$v = $this->quote( stripslashes($v) );
                }
                if (!$this->hasOperator($k)) {
                    $k .= ' =';
                }
            } else {
                $k = $this->identifiers($k);
            }
            $this->arrayWhere[] = $prefix . $k . $v;

            if ($this->arrayCaching) {
                $this->arrayCacheWhere[] = $prefix . $k . $v;
                $this->arrayCacheExists[] = 'where';
            }
        }

        return $this;
    }

    /**
     * Searches for a standard MySQL operator in a specified string
     *
     * @access private
     * @param string $string
     * @return boolean true if found or FALSE if not found
     */
    final public function hasOperator($string)
    {
        $string = trim($string);
        //echo $string;
        $matches = array();
        if (!preg_match("/(\<|>|!|=|is null|BETWEEN|is not null)/i", $string, $matches)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Escapes the specified string
     *
     * @access private
     * @param string $string the String to escape
     * @return string The escaped string
     */
    final public function escape($string)
    {
        return $string; //Joomla will escape the string??
    }

    /**
     * Adds a JOIN query statement
     *
     * @param string $table the table name
     * @param string $cond The JOIN condition
     * @param string $type The type of JOIN e.g LEFT, RIGHT etc.
     * @return TuiyoDatabase
     */
    final public function join($table, $cond, $type = 'LEFT')
    {

        if ($type != '') {
            $type = strtoupper(trim($type));

            if (!in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'))) {
                $type = '';
            } else {
                $type .= ' ';
            }
        }
        // Extract any aliases that might exist.  We use this information
        // in the _protect_identifiers to know whether to add a table prefix
        $this->traceTableAlias($table);

        // Strip apart the condition and protect the identifiers
        if (preg_match('/([\w\.]+)([\W\s]+)(.+)/', $cond, $match)) {
            $match[1] = $this->identifiers($match[1]);
            $match[3] = $this->identifiers($match[3]);

            $cond = $match[1] . $match[2] . $match[3];
        }
        // Assemble the JOIN statement
        $join = $type . 'JOIN ' . $this->identifiers($table) . ' ON ' . $cond;

        $this->arrayJoin[] = $join;
        if ($this->arrayCaching === TRUE) {
            $this->arrayCacheJoin[] = $join;
            $this->arrayCacheExists[] = 'join';
        }

        return $this;
    }

    /**
     * Adds a where not exists subquery to the SQL query
     * NOT EXISTS( SELECT 1 FROM #__tuiyo_users as p where p.userID = u.id )
     * @param string $table The table withing which to search
     * @param string $field The field to search in this table
     * @param string $searchfor The results from the field your searchfor in the table
     */
    final public function whereNotExists($table, $field, $searchfor)
    {

        //NOT EXISTS( SELECT 1 FROM #__tuiyo_users as p where p.userID = u.id )
        if (empty($table) || empty($field) || empty($searchfor)) {
            return $this;
        }
        $whereNotExists = 'SELECT 1 FROM ' . $this->identifiers($table) . ' WHERE ' . $this->escape($field) . '=' . $this->escape($searchfor);

        $this->arrayWhereNotExists[] = $whereNotExists;
        $this->arrayWhere[] = 'NOT EXISTS (' . $whereNotExists . ')';

        if ($this->arrayCaching) {
            $this->arrayCacheWhere[] = 'NOT EXISTS (' . $whereNotExists . ')';
            $this->arrayCacheExists[] = 'where';
        }

        return $this;
    }

    /**
     * Where the required record ($key) is between $value1 and $value2
     *
     * @param type $key
     * @param type $value1
     * @param type $value2
     *
     */
    final public function between($key, $value1, $value2)
    {

        return $this->where($key . " BETWEEN ", $value1 . " AND " . $value2);

    }

    /**
     * Returns the result set from a compiled Query representing
     * the where conditions.
     *
     * See {@method where} for a more controlled approach
     *
     * @param mixed $key string or array. see {@method where}
     * @param string $values
     * @param string $type , e.g AND, OR
     * @return object TuiyoDatabaseResult
     */
    final public function getWhere($key, $values = NULL, $type = 'AND')
    {
        return $this->where($key, $values, $type);
    }

    /**
     * adds a NOT IN clause to the statement
     *
     * @param string $key
     * @param array $values
     * @return ActiveRecord
     */
    final public function notIn($key, $values)
    {
        if (empty($key) || empty($values)) {
            return $this;
        }

        if (!is_array($values)) {
            $values = array($values);
        }
        return $this->in($key, $values, TRUE);
    }

    /**
     *
     * @param mixed $key
     * @param string $values
     * @param boolean $not
     * @param string $type
     * @return object TuiyoDatabase
     */
    final public function in($key, $values, $not = FALSE, $type = 'AND')
    {

        if (empty($key) || empty($values)) {
            return $this;
        }

        if (!is_array($values)) {
            $values = array($values);
        }
        $not = ($not) ? ' NOT' : '';

        foreach ($values as $value) {
            $this->arrayWhereIn[] = $this->escape($value);
        }

        $prefix = (count($this->arrayWhere) == 0) ? '' : $type;

        $whereIn = $prefix . $this->identifiers($key) . $not . " IN (" . implode(", ", $this->arrayWhereIn) . ") ";

        $this->arrayWhere[] = $whereIn;
        if ($this->arrayCaching === TRUE) {
            $this->arrayCacheWhere[] = $whereIn;
            $this->arrayCacheExists[] = 'where';
        }
        // reset the array for multiple calls
        $this->arrayWhereIn = array();
        return $this;
    }

    /**
     * OR LIKE
     *
     * @param string $field
     * @param string $match
     * @param string $side
     * @param string $not
     * @return object ActiveRecord
     */
    final public function orLike($field, $match = '', $side = 'both', $not = '')
    {
        return $this->like($field, $match, 'OR', $side, $not);
    }

    /**
     * Adds a LIKE cluase to the query statement
     *
     * @param string $field
     * @param string $match
     * @param string $type
     * @param string $side
     * @param string $not
     * @return ActiveRecord
     */
    final public function like($field, $match = '', $type = 'AND ', $side = 'both', $not = '')
    {
        if (!is_array($field)) {
            $field = array($field => $match);
        }
        foreach ($field as $k => $v) {
            $k = $this->identifiers($k);

            $prefix = (count($this->arrayLike) == 0) ? '' : $type;
            $v = $this->escape($v);
            $likeStatement = '';
            if ($side == 'before') {
                $likeStatement = $prefix . " $k $not LIKE '%{$v}'";
            } elseif ($side == 'after') {
                $likeStatement = $prefix . " $k $not LIKE '{$v}%'";
            } else {
                $likeStatement = $prefix . " $k $not LIKE '%{$v}%'";
            }

            $this->arrayLike[] = $likeStatement;
            if ($this->arrayCaching === TRUE) {
                $this->arrayCacheLike[] = $likeStatement;
                $this->arrayCacheExists[] = 'like';
            }
        }
        return $this;
    }

    /**
     * Adds an OR NOT LIKE to the statement
     *
     * @param string $field
     * @param string $match
     * @param string $side
     * @return object ActiveRecord
     */
    final public function orNotLike($field, $match = '', $side = 'both')
    {
        return $this->like($field, $match, 'OR', $side, 'NOT');
    }

    /**
     * Adds a NOT LIKE to the statement
     *
     * @param string $field
     * @param string $match
     * @param string $type
     * @param string $side
     * @return object ActiveRecord
     */
    final public function notLike($field, $match = '', $type = 'AND', $side = 'both')
    {
        return $this->like($field, $match, $type, $side, 'NOT');
    }

    /**
     * Adds a group by clause to a query statement
     *
     * @param string $by
     * @return ActiveRecord
     */
    final public function groupBy($by)
    {
        if (is_string($by)) {
            $by = explode(',', $by);
        }

        foreach ($by as $val) {
            $val = trim($val);

            if ($val != '') {
                $this->arrayGroupBy[] = $this->identifiers($val);

                if ($this->arrayCaching === TRUE) {
                    $this->arrayCacheGroupBy[] = $this->identifiers($val);
                    $this->arrayCacheExists[] = 'groupby';
                }
            }
        }
        return $this;
    }

    /**
     * Adds a distinct clause to a select query statement
     *
     * @param boolean $boolean
     * @return ActiveRecord
     */
    final public function distinct($boolean = TRUE)
    {
        $this->arrayDistinct = (is_bool($boolean)) ? $boolean : TRUE;
        return $this;
    }

    /**
     * Adds a having clause to a query statement
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param boolean $escape
     * @return ActiveRecord
     */
    final public function having($key, $value = '', $type = 'AND ', $escape = TRUE)
    {
        if (!is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $k => $v) {
            $prefix = (count($this->arrayHaving) == 0) ? '' : $type;

            if ($escape === TRUE) {
                $k = $this->identifiers($k);
            }

            if (!$this->hasOperator($k)) {
                $k .= ' = ';
            }

            if ($v != '') {
                $v = ' ' . $this->escape($v);
            }

            $this->arrayHaving[] = $prefix . $k . $v;
            if ($this->arrayCaching === TRUE) {
                $this->arrayCacheHaving[] = $prefix . $k . $v;
                $this->arrayCacheExists[] = 'having';
            }
        }

        return $this;
    }

    /**
     * Adds a limit to the query statement
     *
     * @param interger $value
     * @param interger $offset
     * @return object ActiveRecord
     */
    final public function limit($value, $offset = '')
    {
        $this->arrayLimit = $value;
        if ($offset != '') {
            $this->arrayOffset = $offset;
        }
        return $this;
    }

    /**
     * Adds an offset to the query statement
     *
     * @param interger $offset
     * @return object ActiveRecord
     */
    final public function offset($offset)
    {
        $this->arrayOffset = $offset;
        return $this;
    }

    /**
     * Prepares and runs a Query;
     *
     * @return type
     */
    final public function run()
    {

        return $this->prepare()->execute();

    }

    /**
     * Runs a compiled Statement, i.e prepare
     *
     * @return object Statement
     */
    final public function prepare()
    {

        $DB = $this->DBO;
        $QUERY = $this->compile();

        //echo $QUERY;
        //Compile the query;
        $STATEMENT = $DB->prepare($QUERY);

        //Return the Result Object;
        return $STATEMENT;
    }

//    /**
//     * Gets an instance of the active record accessory methods
//     *
//     * @staticvar array $instance
//     * @param object $dbo Database
//     * @param mixed $options
//     * @return object Accessory Methods
//     */
//    public static function getInstance($options = array()) {
//
//        static $instance;
//
//        if (!isset($options) || !is_array($options) || !isset($options['driver'])) {
//
//            exit('we need to know what driver and have a dbo passed');
//            //@TODO show some form of error; Or attempt to get it from the dbo passed;
//            return false;
//        }
//
//        if (!isset($instance[$options['driver']])) {
//            $instance[$options['driver']] = \call_user_func("Library\Database\Drivers\\" . $options['driver'] . "\Accessory::getInstance", $options);
//        }
//
//        //If the class was already instantiated, just return it
//        return $instance[$options['driver']];
//    }

    final public function setValues($key, $value = '', $escape = NULL)
    {

        //$key = $this->_object_to_array($key);
        if (is_object($key)) {

            $array = array();
            foreach (get_object_vars($key) as $var => $val) {
                // There are some built in keys we need to ignore for this conversion
                if (!is_object($val) && !is_array($val)) {
                    $array[$var] = $val;
                }
            }
            unset($key);
            $key = $array;
        }

        if (!is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $k => $v) {
            if ($escape === FALSE) {
                $this->arraySet[$this->identifiers($k)] = $v;
            } else {
                //echo $this->identifiers($k);
                $this->arraySet[$this->identifiers($k)] = $this->escape($v);
            }
        }

        return $this;
    }

    /**
     * Inserts a record to a dbms table
     *
     */
    abstract public function insert($table = '', $set = NULL, $updateIfExists = FALSE, $updateUnique = NULL);

    /**
     * Deletes a record from a dbms table
     */
    abstract public function delete($table = '', $where = '', $limit = NULL, $resetData = TRUE);

    /**
     * Updates a dbms table
     */
    abstract public function update($table = '', $set = NULL, $where = NULL, $limit = NULL);

}