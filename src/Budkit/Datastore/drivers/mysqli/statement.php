<?php

namespace Budkit\Datastore\Drivers\MySQLi;

use Budkit\Datastore\Results;

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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database/drivers/mysql/statement
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
final class Statement extends Results {


    /**
     * Constructs the statement object
     * 
     * @param array $options
     */
    public function __construct(Driver $driver) {

        $this->setDBO( $driver );

    }

    /**
     * Executes the prepared Statement
     *
     * @return
     */
    public function execute() {

        $DB = $this->getDBO();
        //Run the Query;
        $resultId = $DB->exec();
        //\Platform\Debugger::log($resultId);

        $this->setResultId( $resultId )->setConnectionId($DB->getResourceId())->setAffectedRows($this->getAffectedRows());
        
        $DB->resetRun();

        return $this;
    }

    /**
     * Explains a query
     * 
     * @return 
     */
    public function explain() {
        
    }

    /**
     * Counts the number of rows in a result set
     * 
     * @return interger
     */
    public function rowCount() {

        return @mysqli_num_rows($this->getResultId());
    }

    /**
     * Counts columns in a result row
     * 
     * @return interger
     */
    public function columnCount() {
        return @mysqli_num_fields($this->getResultId());
    }

    /**
     * Lists all columns in a result row
     * 
     * @return array
     */
    public function listColumns() {
        $fieldNames = array();
        $field = NULL;
        while ($field = mysqli_fetch_field($this->getResultId())) {
            $fieldNames[] = $field->name;
        }

        return $fieldNames;
    }

    /**
     * Returns metadata for a column or all columns in a result set
     *
     * @return stdClass
     */
    public function getColumnMeta($name = '') {

        $retval = array();
        $field = NULL;
        while ($field = mysqli_fetch_field($this->getResultId())) {
            $f = new stdClass();
            $f->name = $field->name;
            $f->type = $field->type;
            $f->default = $field->def;
            $f->maxLength = $field->max_length;
            $f->primaryKey = $field->primary_key;

            $retval[] = $f;
        }

        return $retval;
    }

    /**
     * Frees the result
     *
     * @return void
     */
    public function freeResults() {
        if (is_a($this->getResultId(), "mysqli_result")) {
            mysqli_free_result($this->getResultId());
            $this->setResultId(FALSE);
        }
    }

    /**
     * Moves the internal data result pointer
     *
     * @param interger $n
     * @return boolean
     */
    public function dataSeek($n = 0) {
        return mysqli_data_seek($this->getResultId(), $n);
    }

    /**
     * Fetches a result row as an associative array
     * 
     * @return array
     */
    public function fetchAssoc() {

        //echo $this->getResultId();

        return mysqli_fetch_array($this->getResultId(), MYSQLI_ASSOC);
    }

    /**
     * Returns a row in a result set
     * 
     * @return array 
     */
    public function fetch() {

        //echo $this->getResultId();

        return mysqli_fetch_row($this->getResultId());
    }

    /**
     * Fetches the next row and returns it as an object.
     * 
     * @return object
     */
    public function fetchObject() {

        return mysqli_fetch_object($this->getResultId());
    }
    
    
    /**
     * Returns the ID of the last inserted row
     * 
     * @return interger
     */
    public function lastInsertId(){
        return mysqli_insert_id( $this->getResultId() ); 
    }
}
