<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 02/07/2014
 * Time: 17:29
 */

namespace Budkit\Routing;

use Budkit\Application\Support\Mock;
use Budkit\Application\Support\Mockable;
use Budkit\Dependency\Container;
use Budkit\Protocol\Request;
use Budkit\Protocol\Response;

class Router implements Mockable {

    protected $collection;
    protected $container;
    protected $matchedRoute = null;
    protected $failedRoute  = null;
    protected $testedRoutes = [];

    use Mock;

    public function __construct(Collection $collection, Container $container) {

        $this->collection = $collection;
        $this->container  = $container;
        //$this->container->shareInstance( $collection , "routes" );
    }

    /**
     *
     * Get the first of the closest-matching failed routes.
     *
     * @return Route
     *
     */
    public function getFailedRoute() {
        return $this->failedRoute;
    }

    public function getRouteCollection(){

        return $this->collection->getRoutes();

    }

    /**
     *
     * Returns the result of the call to match() again so you don't need to
     * run the matching process again.
     *
     * @return Route|false|null Returns null if match() has not been called
     * yet, false if it has and there was no match, or a Route object if there
     * was a match.
     *
     */
    public function getMatchedRoute() {
        return $this->matchedRoute;
    }


    public function matchToRoute(Request $request) {

        $routes = $this->collection->getRoutes();

        //print_r($routes);

        foreach ($routes as $route) {
            $this->testedRoutes[] = $route;
            if ($route->matches($request)) {
                $this->matchedRoute = $route;
                //@TODO trigger route.matched if a route has been found
                //break;	
                return $this->matchedRoute;
            }
            //Record the last failed match;
            if (!$this->failedRoute || $route->score > $this->failedRoute->score) {
                $this->failedRoute = $route;
            }

        }
        //@TODO trigger route.not.matched if a route not found;
        $this->matchedRoute = false;

        return false;
    }

    public function getRoute( $name ){

        if(!isset($this->collection[$name])) return null;

        return $this->collection[ $name ];
    }

    public function getTestedRoutes() {
        return $this->testedRoutes;
    }

    public function __call($method, $parameters) {

        return call_user_func_array([&$this->collection, $method], $parameters);

        //print_r($this->collection);
    }

    //Makes the Router object a proxy for the Collection.

    protected function makeResponse(Request $request, Response $response) {
        //Its important to note that there may be several matched routes;
        //Unless a send response is explicitly called in any of the routes
        //Should we cascade the responses generated by each route from one to the next;
        //eventually return the make response;

        // return $response->make($Request); prepare the response, setting the headers etc;
    }
} 