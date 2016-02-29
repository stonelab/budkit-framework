<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * table.php
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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database/table
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 */

namespace Budkit\Datastore;

use Budkit\Helper\Object;
use Budkit\Validation\Validate;
use Exception;

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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database/table
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
abstract class Table extends Object
{


    protected $validator;


    /**
     * The databse object
     *
     * @var type
     */
    protected $dbo;


    /**
     * Constructs the table object
     *
     * @param type $options
     */
    public function __construct($table = "", Driver $driver)
    {


        $this->dbo = $driver;

        $this->validator = $driver->getValidator();

        //check if table exist and describe schema if blank
        if (!is_null($table)) {
            $this->setTableName($driver->replacePrefix($table));
            $this->describe();
        }

    }

    /**
     * Defines the table name;
     *
     * @param type $name
     * @return type
     */
    final public function setTableName($name)
    {

        $this->name = preg_replace('/[^A-Z0-9_\.-]/i', '', $name);

        return true;
    }

    abstract public function describe();

    /**
     * Gets a field value
     *
     * @param type $field
     * @return type
     */
    final public function __get($field)
    {
        if (isset($this->fields[$field])) {
            return $this->fields[$field];
        }
        return null;
    }

    /**
     * Sets a dynamique field value
     *
     * @param type $field
     * @param type $value
     * @return Table
     */
    final public function __set($field, $value)
    {
        $this->fields[$field] = $value;
        return $this;
    }

    /**
     * Magic call method for table.
     *
     * This method can help with calling active record type methods in tables directly
     * for instance, $this->select('*')->prepare(); or $this->delete();
     *
     * @param type $method
     * @param type $argument
     */
    final public function __call($method, $arguments)
    {

        $engine = $this->database;


        if (!\is_callable([$engine, $method])) {
            throw new \Exception("The requested Database::{$method} is not not callable");
            return false;
        }

        //Call the method on the table;
        return @\call_user_func_array([$engine->from($this->getTableName()), $method], $arguments);
    }

    /**
     * Returns the table name
     *
     * @return type
     */
    final public function getTableName()
    {

        return $this->name;
    }

    /**
     * Binds user data to the table;
     *
     * @param type $data
     * @param type $ignore
     * @param type $strict
     * @param type $filter
     * @return type
     */
    final public function bindData($data, $ignore = array(), $strict = true, $filter = array())
    {

        $validate = $this->validator;

        if (!is_object($data) && !is_array($data)) {
            throw new Exception(t("Data must be either an object or array"));
            return false;
        }

        $dataArray = is_array($data);
        $dataObject = is_object($data);


        foreach ($this->schema as $k => $v) {

            // internal attributes of an object are ignored
            if (!in_array($k, $ignore)) {
                if ($dataArray && isset($data[$k])) {
                    //If $data[k] is an array?
                    $this->schema[$k]->Value = $data[$k];
                } else if ($dataObject && isset($data->$k)) {
                    $this->schema[$k]->Value = $data->$k;
                }
            }

            //validate. if only just 1 fails, break and throw an error;
            if (isset($this->schema[$k]->Validate) && isset($this->schema[$k]->Value)) {
                $datatype = $this->schema[$k]->Validate;

                $datavalue = $this->schema[$k]->Value;

                if (method_exists($validate, $datatype)) {

                    if (!\call_user_func(array($validate, $datatype), $datavalue)) {
                        //unpair the value
                        unset($this->schema[$k]->Value);

                        //set the error
                        throw new Exception(sprintf(t("%s is not a valid %2s"), $k, $datatype));

                        //throw an exception if in strict mode
                        if ($strict) {
                            break;
                        }
                    }
                }
            }
        }

        //did we have any validation errors
        return (count($this->getErrors()) > 0) ? false : true;
    }

    /**
     * Determines if theh currently bound row will be saved as new
     *
     * @return boolean true if primary key has a value
     */
    final public function isNewRow()
    {

        $primary = $this->keys("primary", 1);

        //If the value of the primary key is empty, then we are adding a new row
        return (empty($primary->Value)) ? true : false;
    }

    abstract public function keys($type);

    final public function getRow()
    {

    }

    final public function getRowValues()
    {

    }

    final public function getRowField()
    {

    }

    /**
     * Gets the value of a field in the current ROW
     *
     * @param type $field
     */
    final public function getRowFieldValue($field)
    {

        //If value exists;
        if (array_key_exists($field, $this->schema)) {
            return $this->schema[$field]->Value;
        }
        return null;
    }

    /**
     * Sets a field value in the current row
     *
     * @param type $field
     */
    final public function setRowFieldValue($field, $value)
    {

        //If value exists;
        if (array_key_exists($field, $this->schema)) {
            $this->schema[$field]->Value = $value;
            return true;
        }
        return false;
    }

    abstract public function load($keyid = null);

    abstract public function save($data = null);

    abstract public function create();

    abstract public function dump();

    abstract public function insert($data = null, $updateIfExists = TRUE);

    abstract public function update($key, $data = null);

    abstract public function truncate();


}
