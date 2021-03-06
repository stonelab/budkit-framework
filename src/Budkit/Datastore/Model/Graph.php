<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * graph.php
 *
 * Requires PHP version 5.3
 *
 * LICENSE: This source file is subject to version 3.01 of the GNU/GPL License
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.txt  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 * send a note to support@stonyhillshq.com so we can mail you a copy immediately.
 *
 * @category   Utilities
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/graph
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 */

namespace Budkit\Datastore\Model;

use Budkit\Datastore\Model\Graph\Edge;
use Budkit\Datastore\Model\Graph\Node;

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
final class Graph
{

    /*
     * @var object 
     */
    static $instance;

    /**
     * A vertex (pl. vertices) or node is the fundamental unit on which a graph is formed.
     * An array object containing graph vertex set.
     *
     * @var type
     */
    protected $nodeSet = array();

    /**
     * Undirected edge between two endpoints in vertice set of undirected graph
     *
     * @var type
     */
    protected $edgeSet = array();

    /**
     * Directed edge between two endpoints in vertex set, Holds
     * Edge IDs that have been added as arcs..
     *
     * @var type
     */
    protected $arcSet = array();

    /**
     * An array of sub graphs objects
     *
     * @var type
     */
    protected $subgraphs = array();

    /**
     * Constructs a new graph.
     *
     * @param type $nodes
     * @param type $edges
     * @param type $directed
     * @param type $graphID
     */
    public function __construct(Array $nodes = [], Array $edges = [], $directed = FALSE, $graphID = NULL)
    {
        //parent::__construct();
        $this->nodeSet = $nodes;
        $this->edgeSet = $edges;

    }


    /**
     * Determines the shortest distance between two nodes
     * d(u,v)
     *
     * @param type $nodeA
     * @param type $nodeB
     *
     * @return array(); An ordered array sequence of nodes from u to v
     */
    public function getPath($nodeA, $nodeB)
    {

    }

    /**
     * The Path length |d(u,v)| is the total number of edges in the path connecting
     * nodeA to nodeB.
     *
     * @param type $nodeA
     * @param type $nodeB
     * @reurns interger a pathlength of zero implies infinity, i.e no path was found
     */
    public function getPathLength($nodeA, $nodeB)
    {

    }

    /**
     * The size of a graph is the number of its edges |E(G)|
     *
     * @return interger
     */
    public function getSize()
    {

        if (empty($this->edgeSet))
            return 0;

        //Count the number of nodes in this graph;
        return count($this->nodeSet);
    }

    /**
     * The order of a graph is the number of its nodes/vertices |V(G)|
     *
     * @return interger
     */
    public function getOrder()
    {

        if (empty($this->nodeSet))
            return 0;

        //Count the number of nodes in this graph;
        return count($this->nodeSet);
    }

    /**
     * Returns an array of edgeIds for directed edges (arcs)
     *
     * @return type
     */
    public function getArcSet()
    {
        return $this->arcSet;
    }

    /**
     * Returns all edges describing this graph
     *
     * @return type
     */
    public function getEdgeSet()
    {
        return $this->edgeSet;
    }

    /**
     * Returns the maximum degree incident on graph nodes
     *
     * @return interger
     */
    public function getMaxDegree()
    {

    }

    /**
     * Returns the minimum degree.
     * Degrees are a represenation of the number of degrees
     * incident to a node.
     */
    public function getMinDegree()
    {

    }

    /**
     * Isolated nodes are nodes with a degree of zero
     * @return array
     */
    public function getIsolated()
    {

    }

    /**
     * Returns all nodes with a degree of 1;
     * @return array;
     */
    public function getLeaves()
    {

    }


    /**
     * Returns a node object if exists in graph
     *
     * @param type $nodeId case sensitive ID of the node requested
     * @return object $node if found;
     *
     */
    public function getNode( $nodeId )
    {
        static $instance = [];

        if (isset($instance[$nodeId]))
            return $instance[$nodeId];

        $nodes = $this->nodeSet;

        if (empty($nodes))
            return NULL;

        foreach ($nodes as $node):
            if ($node->getId() == $nodeId):
                    $instance[$nodeId] = &$node;
                    return $instance[$nodeId];
                break;
            endif;
        endforeach;

        return NULL;
    }

    /**
     * Adds an edge between two node endpoints.
     *
     * @param type $nodeA head (Subject)
     * @param type $nodeB tail (Object)
     * @param type $name
     * @param type $data
     * @param type $directed
     * @param type $weight
     * @return boolean
     */
    public function addEdge(Node &$nodeA, Node &$nodeB, $name = NULL, $directed = TRUE, $data = array(), $weight = 0)
    {

        $edge = new Edge($nodeA, $name, $nodeB, $data, $directed, $weight); //Will need to decide whether to use nodeAId-nodeBId as edgeId
        $edgeId = $edge->getId();

        //Directed edges have their Id's referenced in arcSet
        if ($directed && !in_array($edgeId, $this->arcSet))
            $this->arcSet[] = $edgeId;

        //@TODO This is not the ideal way to set parrallel edges, Parralel edges connect the same pair of nodes
        //If edge already exists, increment the weight;
        if (isset($this->edgeSet[$edgeId])) {
            $this->edgeSet[$edgeId]->weight++;
            $edgeData = $this->edgeSet[$edgeId]->getData();
            $data = array_merge($edgeData, $data);

            //Makeing a directed array undirected
            if (!$directed && in_array($edgeId, $this->arcSet))
                $this->arcSet = array_diff($this->arcSet, array($edgeId));
            return $edge;
        }
        //array_merge edge data
        if (!isset($this->edgeSet[$edgeId]))
            $this->edgeSet[$edgeId] = &$edge;

        //If directed, use edgeIsArc to indicate;
        return $edge;
    }

    /**
     * Removes a node from the graph
     *
     * @param type $nodeId
     */
    public function removeNode($nodeId)
    {
        if (isset($this->nodeSet[$nodeId]))
            unset($this->nodeSet[$nodeId]);
        return true;
    }


    /**
     * The number of head endpoints adjacent to the node is called the indegree.
     * i.e Number of directed edges (arcs) to this node. Use getDegree to get
     * the total number of edges (directed or undirected) to this node
     *
     * @return interger
     */
    public function getInDegree(Node $ofNode)
    {

        $graph = $this;
        $arcIds = $graph->getArcSet();

        //If this graph is undirected, then we can't calculate the  indegree to this node;
        if (empty($arcIds))
            return $this->getDegree();

        $edges = $graph->getEdgeSet();
        $incidence = 0;

        //search for all arcs with this node as tail
        foreach ($arcIds as $arc):
            $edge = $edges[$arc];
            if ($edge->getTail()->getId() == $ofNode->getId()) {
                $incidence++;
                //if head is the same as self, as is the case in cycled edges then we have one more indegree,
                //looped vertices have an indegree of two;
                if ($edge->getHead()->getId() == $ofNode->getId())
                    $incidence++;
            }
        endforeach;

        return $incidence;
    }

    /**
     * Degree or valency is the number of edges incident to the vertex
     * deg(v) where v = vertex or node
     */
    public function getDegree(Node $ofNode )
    {

        $incidence = 0;
        $graph = $this;
        $edges = $graph->getEdgeSet();

        //If this graph is undirected, then we can't calculate the  indegree to this node;
        if (empty($edges))
            return $incidence;

        //search for all arcs with this node as tail
        foreach ($edges as $edge):
            if ($edge->getTail()->getId() == $ofNode->getId()) {
                $incidence++;
                //if head is the same as self, as is the case in cycled edges then we have one more indegree,
                //looped vertices have an indegree of two;
                if ($edge->getHead()->getId() == $ofNode->getId())
                    $incidence++;
            } elseif ($edge->getHead()->getId() == $ofNode->getId()) {
                //Cover for cycles
                if ($edge->getHead()->getId() == $ofNode->getId())
                    $incidence++;
            }
        endforeach;

        return $incidence;
    }

    /**
     * The number of tail endpoints adjacent to the node is called the outdegree
     * i.e Number of directed edges (arcs) from this node. Use getDegree to get
     * the total number of edges (directed or undirected) to this node
     *
     * @return interger
     */
    public function getOutDegree(Node $ofNode )
    {

        $graph = $this;
        $arcIds = $graph->getArcSet();

        //If this graph is undirected, then we can't calculate the  indegree to this node;
        if (empty($arcIds))
            return $this->getDegree();

        $edges = $graph->getEdgeSet();
        $incidence = 0;

        //search for all arcs with this node as tail
        foreach ($arcIds as $arc):
            $edge = $edges[$arc];
            if ($edge->getHead()->getId() == $ofNode->getId()) {
                $incidence++;
                //if head is the same as self, as is the case in cycled edges then we have one more indegree,
                //looped vertices have an indegree of two;
                if ($edge->getTail()->getId() == $ofNode->getId())
                    $incidence++;
            }
        endforeach;

        return $incidence;
    }

    public function removeEdgeWithId($edgeId){
        if(isset($this->edgeSet[$edgeId])){

            $edge = $this->edgeSet[$edgeId];

            //Remove the value from the arcSet array
            $this->arcSet = array_diff($this->arcSet, array($edge->getId()));

            //Remove the value from the edgeSet array;
            unset($this->edgeSet[$edgeId]);

        }

    }

    /**
     * Removes an Edge from the graph. Use removeArc to remove directed edges
     *
     * @param type $head
     * @param type $tail
     * @param type $directed if false, will remove all incident edges of the kind head-tail or tail-head
     * @return boolean
     * @throws \Platform\Exception
     */
    public function removeEdgeWithNodes(Node &$head, Node &$tail, $directed = TRUE)
    {

        if (!is_a($head, Node::class) || !is_a($tail, Node::class)) {
            throw new \Exception("Nodes used to create a new Edge must be instances of {Node::class}");
            return false;
        }

        $_nodeIds = array($head->getId(), $tail->getId());
        $edges = $this->edgeSet;
        //find all edges with these two nodes incident
        foreach ($edges as $edge):
            echo $edge->getId() . "<br />";
            if (in_array($edge->getHead()->getId(), $_nodeIds) && in_array($edge->getTail()->getId(), $_nodeIds)):
                if (!$directed):
                    unset($this->edgeSet[$edge->getId()]);
                elseif ($edge->getHead()->getId() == reset($_nodeIds) && $edge->getTail()->getId() == end($_nodeIds)):
                    //Remove the value from the arcSet array
                    $this->arcSet = array_diff($this->arcSet, array($edge->getId()));
                    unset($this->edgeSet[$edge->getId()]);
                endif;
            endif;
        endforeach;

        return true;
    }

    /**
     * Checks if the current graph is a directed graph
     *
     * @return boolean true if directed and false if not;
     */
    public function isDirected()
    {
        //if size of arcSet is greater than1 , then this graph is directed;
        if (empty($this->arcSet))
            return false;

        return true;
    }

    /**
     * Creates and adds a Node to the graph if none, already exists
     *
     * @param type $nodeId
     * @param type $data
     */
    public function createNode($nodeId, $data = array())
    {
        $node = new Node($nodeId, $data);

        //@mark Add node will ignore node if it already exists;
        $this->addNode($node);

        return $node;
    }

    /**s
     * Adds a node to the current graph
     *
     * @param type $node
     */
    public function addNode(Node &$node)
    {
        //Nodes must be an instance graph Node;
        if (!$this->isNode($node)) {
            throw new \Exception("Node must be an instance of {Node::class}");
            return false;
        }
        $nodedId = $node->getId();
        if (!empty($nodedId) && !isset($this->nodeSet[$node->getId()])) {
            $this->nodeSet[$node->getId()] = &$node;
        }
        return $this;
    }

    /**
     * Checks if a node is a node object
     *
     * @param type $node
     * @throws boolean
     */
    private function isNode(Node &$node)
    {
        //Nodes must be an instance graph Node;
        if (!is_a($node, Node::class )) {
            return false;
        }
        return true;
    }

    /**
     * Adds a directed edge (arc) to two nodes in graph.
     * If no arcUid is provided will add an undirected edge
     *
     *
     * @param type $name
     * @param type $nodeA
     * @param type $nodeB
     * @param type $data
     * @param type $directed
     * @param type $weight
     */
    private function edgeIsArc($edgeId)
    {

    }

}

