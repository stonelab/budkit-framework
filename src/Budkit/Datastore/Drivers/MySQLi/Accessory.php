<?php


namespace Budkit\Datastore\Drivers\MySQLi;

use Budkit\Datastore\Activerecord;


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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database/drivers/mysql/accessory
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
class Accessory extends Activerecord
{

    /**
     * Construst the Accessory Class
     *
     * @return void
     */
    public function __construct(Driver $dbo)
    {
        //Set the DBO Object
        $this->DBO = $dbo;
    }

    /**
     * Returns an instance of the acessory class
     *
     * @staticvar self $instance
     * @param type $options
     * @return self
     */
    public static function getInstance($options = array())
    {

        static $instance;

        //If the class was already instantiated, just return it
        if (isset($instance))
            return $instance;

        $instance = new self();

        return $instance;
    }

    /**
     *
     * @param <type> $table
     * @param <type> $set
     */
    final public function insert($table = '', $set = NULL, $updateIfExists = FALSE, $updateUnique = NULL, $autocexecute = TRUE)
    {

        if (!is_null($set)) {
            //print_R($set);
            $this->setValues($set);
        }

        if (count($this->arraySet) == 0) {
            $this->setError(_t("There are no values to insert into the database"));
            if ((bool)$this->_debug) {
                return FALSE; //display error
            }
            return FALSE;
        }

        if ($table == '') {
            if (!isset($this->arrayFrom[0])) {
                $this->setError(_t("No table to insert data to defined"));
                if ((bool)$this->_debug) {
                    return FALSE; //display error
                }
                return FALSE;
            }
            $table = $this->arrayFrom[0];
        }

        $table = $this->DBO->identifiers($table, TRUE, NULL, FALSE);
        $keys = array_keys($this->arraySet);
        $values = array_values($this->arraySet);
        $keyword = ($updateIfExists) ? "REPLACE" : "INSERT";
        $shortgunsql = $this->query = "{$keyword} INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ")";


        $this->DBO->resetRun();

        //@TODO somepeople might want to know what the inserted ID is?
        if (!$autocexecute) {
            return $shortgunsql;
        }

        return $this->DBO->exec($shortgunsql); //this returns a cursor. will need to check for errors and other stuff

    }

    /**
     *
     * @param <type> $table
     * @param <type> $where
     * @param <type> $limit
     * @param <type> $resetData
     */
    final public function delete($table = '', $where = '', $limit = NULL, $resetData = TRUE)
    {


        if ($table == '') {
            if (!isset($this->arrayFrom[0])) {
                if ($this->debug) {
                    //@TODO some interesing error
                    return false;
                }
                return FALSE;
            }
            $table = $this->arrayFrom[0];
        } elseif (is_array($table)) {
            foreach ($table as $t) {
                $this->delete($t, $where, $limit, FALSE);
            }
            $this->resetRun();
            return;
        } else {
            $table = $this->DBO->identifiers($table);
        }

        if ($where != '') {

            $this->where($where);
        }

        if ($limit != NULL) {
            $this->limit($limit);
        }

        //print_R($this->arrayWhere);

        if (count($this->arrayWhere) == 0 && count($this->arrayWhereIn) == 0 && count($this->arrayLike) == 0) {
            if ($this->debug) {
                //@TODO some interesting error
                return false;
            }
            return FALSE;
        }

        $conditions = '';

        if (count($this->arrayWhere) > 0 OR count($this->arrayLike) > 0) {
            $conditions = "\nWHERE ";
            $conditions .= implode("\n", $this->arrayWhere);

            if (count($this->arrayWhere) > 0 && count($this->arrayLike) > 0) {
                $conditions .= " AND ";
            }
            $conditions .= implode("\n", $this->arrayLike);
        }

        $limit = (!$this->arrayLimit) ? '' : ' LIMIT ' . $this->arrayLimit;

        $sql = "DELETE FROM " . $table . $conditions . $limit;
        $shortgunsql = $this->query = $this->DBO->replacePrefix($sql);

        return $this->DBO->exec($shortgunsql);
    }

    /**
     *
     * @param <type> $table
     * @param <type> $set
     * @param <type> $where
     * @param <type> $limit
     */
    final public function update($table = '', $set = NULL, $where = NULL, $limit = NULL)
    {

        if (!is_null($set)) {
            $this->setValues($set);
        }
        if (count($this->arraySet) == 0) {
            if ($this->debug) {
                //@TOTO Exception or much more info
                return false;
            }
            return FALSE;
        }
        if ($table == '') {
            if (!isset($this->arrayFrom[0])) {
                if ($this->debug) {
                    return false;
                }
                return FALSE;
            }
            $table = $this->arrayFrom[0];
        }
        if (!empty($where) && is_array($where)) {

            //print_R($where);

            $this->where($where);

            //print_R($this);
            //print_R($this->arrayWhere);
        }
        if ($limit != NULL) {
            $this->limit($limit);
        }
        //Now update;
        foreach ($this->arraySet as $key => $val) {
            $valstr[] = $key . " = " . $val;
        }

        $limit = (!$limit) ? '' : ' LIMIT ' . $limit;
        $table = $this->DBO->identifiers($table);
        $orderby = (isset($this->arrayOrderBy) && count($this->arrayOrderBy) >= 1) ? ' ORDER BY ' . implode(", ", $this->arrayOrderBy) : '';

        $sql = "UPDATE " . $table . " SET " . implode(', ', $valstr);
        $sql .= (isset($this->arrayWhere) && count($this->arrayWhere) >= 1) ? " WHERE " . implode(" ", $this->arrayWhere) : '';
        $sql .= $orderby . $limit;

        $shortgunsql = $this->query = $this->DBO->replacePrefix($sql);

        //echo $shortgunsql."<br />";
        $this->DBO->resetRun();

        return $this->DBO->exec($shortgunsql);
    }

}