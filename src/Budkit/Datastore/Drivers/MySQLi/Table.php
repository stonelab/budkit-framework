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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database/drivers/mysql/table
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 */

namespace Budkit\Datastore\Drivers\MySQLi;


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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database/drivers/mysql/table
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
class Table extends \Budkit\Datastore\Table
{

    /**
     * Current Table Name
     *
     * @var type
     */
    protected $name = '';

    /**
     * Holds the create statement of the table
     *
     * @var type
     */
    protected $schema = array();


    /**
     * Loads a table row by KeyId
     *
     * @param type $keyid
     */
    public function load($keyid = null)
    {

    }

    /**
     * Saves changes to the database
     *
     * @param type $data
     * @return type
     */
    public function save($data = null)
    {

        //1. Check if we have data and deal with it!
        if (!is_null($data)) {
            if (!$this->bindData($data)) {
                //we could not save the data
                return false;
            }
        }
        //2. Check if the primary key has a value
        $primary = $this->keys();

        //3. if the primary key has a value, then we are updating, else we are inserting
        if (isset($primary->Value) && !empty($primary->Value)) {
            return $this->update($primary->Value);
        } else {
            return $this->insert();
        }
        //if we get here then there is a problem
        return false;
    }

    /**
     * Returns all fields corresponding to the key type
     *
     * @param string $type primary, unique, multiple
     */
    public function keys($type = 'primary', $limit = 1)
    {

        $valid = array("primary" => 'PRI', "unique" => 'UNI', "multiple" => 'MUL');

        //Check if we have a valid key Type;
        if (empty($this->schema) || !array_key_exists($type, $valid)) {
            return array();
        }

        $keys = array();
        $i = 0;

        foreach ($this->schema as $field => $schema) {

            if ($limit > 0 && $i >= $limit)
                break; //useful with compound keys

            if (strval($schema->Key) == $valid[$type]) {
                $keys[$field] = $schema;
            }
            $i++;
        }

        $return = (array)$keys;

        if ($limit === 1 && !empty($return)) {
            $fieldname = array_keys($return);
            $return = $return[$fieldname[0]];
        }
        return $return;
    }

    /**
     * Updates the database table field value
     *
     * @param type $key
     * @param type $data
     * @return type
     */
    public function update($key, $data = null)
    {
        //1. Check if we have data and deal with it!
        if (!is_null($data)) {
            if (!$this->bindData($data)) {
                //we could not save the data
                return false;
            }
        }
    }

    /**
     * Inserts a new record to the database
     *
     * @param type $data
     * @return type
     */
    public function insert($data = null, $updateIfExists = FALSE)
    {
        //1. Check if we have data and deal with it!
        if (!is_null($data)) {
            if (!$this->bindData($data)) {
                //we could not save the data
                return false;
            }
        }

        $primary = $this->keys("primary");
        $set = array();

        foreach ($this->schema as $field => $fieldObject) {

            //Skip the primary key for obvious reasons;
            if ($field === $primary->Field) continue;

            //Set the value pair for inserting
            $set[$field] = isset($fieldObject->Value) ? $fieldObject->Value : $fieldObject->Default;

            //Use Quotes in place of empty fields?
            if (empty($set[$field]) || (isset($fieldObject->Validate) && $fieldObject->Validate == 'string')) {
                $set[$field] = $this->dbo->Quote($set[$field]);
            }
        }

        if ($updateIfExists):
            $unique = $primary->Field;
            if (empty($unique)) {
                $updateIfExists = FALSE;
            }
        endif;

        //Insert into the database
        return $this->dbo->insert($this->getTableName(), $set, $updateIfExists, $unique);

    }

    /**
     * Deletes all the records in a tablse
     *
     */
    public function truncate()
    {
        //@TODO : a transaction
    }

    /**
     * Creates a table in the database
     * @param type $schema
     */
    public function create($schema = '')
    {
        //Recreates the table, or a given table
    }

    /**
     * Dumps a table from the database!
     * CAUTION!**
     */
    public function dump()
    {

    }

    /**
     * Describes a table on load
     *
     */
    public function describe()
    {

        $sql = 'DESCRIBE ' . $this->name;
        $schema = $this->dbo->prepare($sql)->execute();

        while ($row = $schema->fetchObject()) {

            if (preg_match('/unsigned/', $row->Type)) {
                $row->Unsigned = true;
            }
            if (preg_match('/^((?:var)?char)\((\d+)\)/', $row->Type, $matches)) {
                //$row->Type      = $matches[1];
                $row->Validate = 'string'; //
                $row->Length = $matches[2];
            } else if (preg_match('/^decimal\((\d+),(\d+)\)/', $row->Type, $matches)) {
                //$row->Type      = 'decimal';
                $row->Validate = 'decimal';
                $row->Precission = $matches[1];
                $row->Scale = $matches[2];
            } else if (preg_match('/^float\((\d+),(\d+)\)/', $row->Type, $matches)) {
                //$row->Type      = 'float';
                $row->Validate = 'float';
                $row->Precission = $matches[1];
                $row->Scale = $matches[2];
            } else if (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row->Type, $matches)) {
                //$row->Type = 'interger'; //$matches[1];
                $row->Validate = 'interger';
                $row->Length = $matches[2];
            }
            if (strtoupper($row->Key) == 'PRI') {
                $row->Primary = true;

                if ($row->Extra == 'auto_increment') {
                    $row->Identity = true;
                } else {
                    $row->Identity = false;
                }
            }

            if (strtoupper($row->Null) == 'NO') {
                $row->Null = 0;
            } else {
                $row->Null = 1;
            }

            $this->schema[$row->Field] = $row;
        }

        //Just something good!
        return true;
    }
}
