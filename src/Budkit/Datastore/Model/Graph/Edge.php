<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * edge.php
 *
 * Requires PHP version 5.3
 *
 * LICENSE: This source file is subject to version 3.01 of the GNU/GPL License 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.txt  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 * send a note to support@stonyhillshq.com so we can mail you a copy immediately.
 *
 * @category   Utility
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/graph
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 * 
 */

namespace Budkit\Datastore\Model\Graph;
use Budkit\Datastore\Model\Graph;

/**
 * What is the purpose of this class, in one sentence?
 *
 * How does this class achieve the desired purpose?
 *
 * @category   Utility
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/graph
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
final class Edge {

    /**
     * Identifies the current edge
     * @var type 
     */
    protected $edgeId = NULL;
    
    /**
     * Adds a name to describe the edge
     * 
     * @var type 
     */
    protected $edgeName = NULL;
    
    /**
     * The edge Head Node
     * @var type 
     */
    protected $edgeHead = NULL;
    
    /**
     * The edge tail Node
     * @var type 
     */
    protected $edgeTail = NULL;
    
    /**
     * Sets an arbitrary number for edge weight
     * @var type 
     */
    public $edgeWeight = 0;

    /**
     * Holds any data associated to this edge
     * 
     * @var type 
     */
    protected $edgeData = array();

    /**
     * Returns the edge's Id
     * 
     * @return type
     */
    public function getId() {
        return $this->edgeId;
    }

    /**
     * Sets the edge Id
     * 
     * @param type $edgeId
     * @return \Platform\Graph\Node
     */
    public function setId($edgeId) {
        $this->edgeId = strval($edgeId);
        return $this;
    }

    /**
     * Sets the edge Name
     * 
     * @param type $edgeName
     * @return \Platform\Graph\Node
     */
    public function setName($edgeName) {
        $this->edgeName = strval($edgeName);
        return $this;
    }

    /**
     * Returns the edge Name if any exists
     * 
     * @return type
     */
    public function getName() {
        return $this->edgeName;
    }

    /**
     * Returns the edge Data if any exists
     * 
     * @return type
     */
    public function getData() {
        return $this->edgeData;
    }

    /**
     * Sets edge data
     * 
     * @param type $edgeData
     * @return \Platform\Graph\Node
     */
    public function setData($edgeData = array()) {
        $this->edgeData = $edgeData;
        return $this;
    }
    
    /**
     * Returns the edge Data if any exists
     * 
     * @return type
     */
    public function &getHead() {
        return $this->edgeHead;
    }

    /**
     * Sets the edge Head;
     * 
     * @param type $edgeHead
     * @return \Platform\Graph\Edge
     */
    public function setHead(&$edgeHead) {
        $this->edgeHead = &$edgeHead;
        return $this;
    }
    
    /**
     * Returns the tail endpoint
     * 
     * @return type
     */
    public function &getTail() {
        return $this->edgeTail;
    }

   /**
    * Sets the edge Tail
    * 
    * @param type $edgeTail
    * @return \Platform\Graph\Edge
    */
    public function setTail(&$edgeTail) {
        $this->edgeTail = &$edgeTail;
        return $this;
    }

    /**
     * Returns and instantiated Instance of the graph class
     * 
     * NOTE: As of PHP5.3 it is vital that you include constructors in your class
     * especially if they are defined under a namespace. A method with the same
     * name as the class is no longer considered to be its constructor
     * 
     * @param type $head
     * @param type $name
     * @param type $tail
     * @param type $edgeData
     * @param type $directed
     * @param type $weight
     * @throws \Platform\Exception
     */
    public function __construct(&$head, $name="", &$tail, $edgeData = array(), $directed = FALSE, $weight = 0) {
        if (!is_a($head, Graph\Node::class) || !is_a($tail, Graph\Node::class)) {
            throw new \Exception("Nodes used to create a new Edge must be instances of Graph\\Node", PLATFORM_ERROR);
        }
        $headId = $head->getId(); //If head is not node return false;
        $tailId = $tail->getId();
        $_edgeId= array($headId); !empty($name)? $_edgeId[]=$name : null; $_edgeId[]=$tailId;
        
        $edgeId = implode(":", $_edgeId);
        
        $this->setId($edgeId);
        $this->setData($edgeData);
        $this->setName($name);
        $this->setHead($head);
        $this->setTail($tail);
    }
}

