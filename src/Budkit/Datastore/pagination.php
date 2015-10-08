<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * pagination.php
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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/output/pagination
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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/output/pagination
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
trait Pagination {


    /**
     * The current state or possition in the system
     * @var static $state
     */
    protected $states = array();

    /**
     * The current total in a navigable record set
     * @var static total
     */
    protected $total = 0;


    /**
     * Pagination class constructor
     * 
     * @return void
     */
    final public function __construct() {

    }

    /**
     * Returns a data model state
     *
     * @param type $state
     */
    public function getState($state, $default = NULL) {
        $state = isset($this->states[$state]) ? $this->states[$state] : $default;
        return $state;
    }

    /**
     * Sets a data model state
     *
     * @param type $state
     * @param type $value
     */
    public function setState($state, $value = NULL) {

        //@todo why do we need previous state?
        $previous = isset($this->states[$state]) ? $this->states[$state] : null;
        $this->states[$state] = $value;

        return $this;
    }

    /**
     * Sets lists limit for page'd lists
     *
     * @param type $limit
     * @return \Platform\Entity
     */
    public function setListLimit($limit = NULL) {
        $this->setState("limit", intval($limit));
        return $this;
    }

    /**
     * Get list start for page'd lists
     *
     * @param type $start
     * @return \Platform\Entity
     */
    public function getListOffset($page = 1, $default = 0) {
        $limit  = $this->getListLimit();
        $offset = $this->getState("limitoffset", intval($default));
        $offset = empty($offset) ? (empty($page)||(int)$page <= 1) ? intval($default) : intval($page-1) * $limit: $offset;

        return $offset;
    }

    /**
     * Gets lists limit for page'd lists
     *
     * @param type $limit
     * @return \Platform\Entity
     */
    public function getListLimit($default = 0) {
        $limit =  $this->getState("limit", intval($default));
        $limit = empty($limit) ? $this->config->getParam("list-length", NULL, "content") : $limit;
        return $limit;
    }

    /**
     * Set list start for page'd lists
     *
     * @param type $start
     * @return \Platform\Model
     */
    public function setListOffset($start = 0) {
        $this->setState("limitoffset", intval($start));
        return $this;
    }

    /**
     * Sets the list total
     *
     * @param type $total
     * @return \Platform\Model
     */
    public function setListTotal($total) {
        $this->total = $total;
        return $this;
    }

    /**
     * Returns the list total;
     *
     * @return type
     */
    public function getListTotal() {
        return $this->total;
    }

    /**
     * Returns a limit clause based on datamodel limit and limitoffset states
     *
     * @return type
     */
    public function getLimitClause($limit = 0) {

        $query = NULL;
        $page  = $this->getState("currentpage", 0);
        $limit = empty($limit) ? (int) $this->getListLimit() : $limit;
        $offset = $this->getListOffset($page, 0);

        if (!empty($limit)):
            $this->setListLimit($limit);
            $this->setListOffset($offset);
            $query = "\nLIMIT {$offset}, {$limit}\t";
        endif;

        //Return limit query
        return $query;
    }

}