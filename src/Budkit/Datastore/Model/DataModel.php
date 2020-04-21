<?php

namespace Budkit\Datastore\Model;

use Budkit\Dependency\Container;
use Budkit\Helper\Helper;

class DataModel extends Helper
{

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
     * The current state of the pages menu
     * @var static pagination
     */
    protected $pagination;



    public function __construct(Container $container)
    {

        $this->config = $container->config;
        $this->database = $container->database;
        $this->container = $container;

    }

    /**
     * Sets the list total
     *
     * @param type $total
     * @return \Platform\Model
     */
    public function setListTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * Returns a limit clause based on datamodel limit and limitoffset states
     *
     * @return type
     */
    public function getLimitClause($limit = 0)
    {

        $query = NULL;
        $page = $this->getState("currentpage", 0);
        $limit = empty($limit) ? (int)$this->getListLimit() : $limit;
        $offset = $this->getListOffset($page, 0);

        if (!empty($limit)):
            $this->setListLimit($limit);
            $this->setListOffset($offset);
            $query = "\nLIMIT {$offset}, {$limit}\t";
        endif;

        //Return limit query
        return $query;
    }

    /**
     * Returns a data model state
     *
     * @param type $state
     */
    public function getState($state, $default = NULL)
    {
        $state = isset($this->states[$state]) ? $this->states[$state] : $default;
        return $state;
    }

    /**
     * Gets lists limit for page'd lists
     *
     * @param type $limit
     * @return \Platform\Entity
     */
    public function getListLimit($default = 0)
    {

        $limit = $this->getState("limit", intval($default));

        //print_R( $this->config->get("content.lists.length"));

        $limit = empty($limit) ? $this->config->get("content.lists.length", 20) : $limit;

        return $limit;
    }

    /**
     * Get list start for page'd lists
     *
     * @param type $start
     * @return \Platform\Entity
     */
    public function getListOffset($page = 1, $default = 0)
    {

        $limit = $this->getListLimit();
        $offset = $this->getState("limitoffset", intval($default));

        $offset = empty($offset) ? (empty($page) || (int)$page <= 1) ? intval($default) : intval($page - 1) * $limit : $offset;

        return $offset;
    }

    /**
     * Sets lists limit for page'd lists
     *
     * @param type $limit
     * @return \Platform\Entity
     */
    public function setListLimit($limit = NULL)
    {

        $this->setState("limit", intval($limit));

        return $this;
    }

    /**
     * Sets a data model state
     *
     * @param type $state
     * @param type $value
     */
    public function setState($state, $value = NULL)
    {

        //@todo why do we need previous state?
        //$previous = isset($this->states[$state]) ? $this->states[$state] : null;
        $this->states[$state] = $value;

        return $this;
    }

    /**
     * Set list start for page'd lists
     *
     * @param type $start
     * @return \Platform\Model
     */
    public function setListOffset($start = 0)
    {
        $this->setState("limitoffset", intval($start));
        return $this;
    }

    public function getPagination()
    {
        if(empty($this->pagination)) $this->setPagination();

        return $this->pagination;
    }

    /**
     * Sets the pagination for the current output if any
     *
     * @return type
     */
    public function setPagination()
    {

        $total = $this->getListTotal();

        if (empty($total))
            return null;

        //Get the current page state from the request;
        $limit = $this->getListLimit();
        $current = $this->getState("currentpage", 1);
        $pages = array();

        //@TODO: Calculates the pages from a recordset or an array of results
        $pages['total'] = ceil($total / $limit);
        $pages['limit'] = $limit;

        //Get the real path to the current page
        $route = $this->container->router->getMatchedRoute();


        $pages['current'] = $route->getURLfromPathWithValues(["page" => strval($current)]);
        //Previous page link
        if (intval($current - 1) > 0):
            $pages['previous'] = $route->getURLfromPathWithValues(["page" => strval($current - 1)] );
        endif;
        //Next page link
        if (intval($current + 1) <= $pages['total']):
            $pages['next'] = $route->getURLfromPathWithValues(["page" => strval($current + 1)] );
        endif;

        //Build the pages;
        for ($i = 0; $i < $pages['total']; $i++):
            $page = $i + 1;
            $pages['pages'][] = array(
                "page_title" => strval($page),
                "page_link" => $route->getURLfromPathWithValues(["page" => $page] ),
                "page_state" => ($page == $current) ? "active" : null,
            );
        endfor;

        //Sets the pagination output;
        if (sizeof($pages['pages']) > 1)
            $this->pagination = $pages;

    }

    /**
     * Returns the list total;
     *
     * @return type
     */
    public function getListTotal()
    {
        return $this->total;
    }

}

