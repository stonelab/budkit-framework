<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 03/07/2014
 * Time: 02:21
 */

namespace Budkit\Routing;

use ArrayAccess;
use Countable;
use IteratorAggregate;

class Collection extends Definition implements ArrayAccess, Countable, IteratorAggregate
{

    protected $routes = [];

    /**
     *
     * A factory to create route objects.
     *
     * @var RouteFactory
     *
     */
    protected $routeFactory;

    /**
     *
     * A prefix to add to each route name added to the collection.
     *
     * @var string
     *
     */
    protected $namePrefix = null;

    /**
     *
     * A prefix to add to each route path added to the collection.
     *
     * @var string
     *
     */
    protected $pathPrefix = null;

    /**
     *
     * A callable to use for each resource attached to the collection.
     *
     * @var callable
     *
     * @see attachResource()
     *
     */
    protected $resourceCallable = null;

    /**
     *
     * A callable to modify to each route added to the collection.
     *
     * @var callable
     *
     * @see add()
     *
     */
    protected $routeCallable = null;


    public function __construct(Factory $routeFactory, array $routes = [])
    {

        $this->routes = $routes;
        $this->routeFactory = $routeFactory;

        $this->setResourceCallable([$this, 'resourceCallable']);
        $this->setRouteCallable([$this, 'routeCallable']);
    }

    /**
     *
     * Sets the callable for attaching resource routes.
     *
     * @param callable $resource The resource callable.
     *
     * @return $this
     *
     */
    public function setResourceCallable($resource)
    {
        $this->resourceCallable = $resource;

        return $this;
    }

    /**
     *
     * Sets the callable for modifying a newly-added route before it is
     * returned.
     *
     * @param callable $callable The callable to modify the route.
     *
     * @return $this
     *
     */
    public function setRouteCallable($callable)
    {
        $this->routeCallable = $callable;

        return $this;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     *
     * IteratorAggregate: returns the iterator object.
     *
     * @return ArrayIterator
     *
     */
    public function getIterator()
    {
        return new ArrayIterator($this->routes);
    } //

    /**
     *
     * Countable: returns the number of routes in the collection.
     *
     * @return int
     *
     */
    public function count()
    {
        return count($this->routes);
    }

    public function addHEAD($uri, $name = null, $action = null)
    {
        return $this->addRoute('HEAD', $uri, $name, $action);
    }

    protected function addRoute($verbs, $path, $name = null, $action = null)
    {
        // create the route with the full path, name, and spec
        $route = $this->routeFactory->newInstance($path, $name, $this->getSpec());

        // add the route
        if (!$route->name) {
            $this->routes[] = $route;
        } else {
            $this->routes[$route->name] = $route;
        }

        $route->addMethod($verbs)->addValues(['action' => $action]);;

        // modify newly-added route
        call_user_func($this->routeCallable, $route);

        // done; return for further modification
        return $route;

    }

    /**
     *
     * Gets the existing default route specification.
     *
     * @return array
     *
     */
    protected function getSpec()
    {
        $vars =
            ['tokens', 'server', 'method', 'accept', 'values', 'secure', 'wildcard', 'routable', 'isMatch', 'generate',
                'namePrefix', 'pathPrefix', 'resourceCallable', 'routeCallable',];

        $spec = [];
        foreach ($vars as $var) {
            $spec[$var] = $this->$var;
        }

        return $spec;
    }

    public function add($uri, $name = null, $action = null)
    {
        $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'];

        return $this->addRoute($verbs, $uri, $name, $action);
    }

    public function offsetSet($name, $route)
    {

        //$route must be of type Route;
        if (!$route instanceof Route) {
            throw new Exception('Can only add routes to the Router Collection');
        }

        //add the route to the collection container;
        if (is_null($name)) {
            $this->routes[] = $route;
        } else {
            $this->routes[$name] = $route;
        }
    }

    public function offsetExists($name)
    {
        return isset($this->routes[$name]);
    }

    public function offsetUnset($name)
    {
        unset($this->routes[$name]);
    }

    public function offsetGet($name)
    {
        return isset($this->routes[$name]) ? $this->routes[$name] : null;
    }

    /**
     *
     * Use the `$resourceCallable` to attach a resource.
     *
     * @param string $name The resource name; used as a route name prefix.
     *
     * @param string $path The path to the resource; used as a route path
     *                     prefix.
     *
     * @return null
     *
     */
    public function attachResource($path, $name)
    {
        $this->attach($path, $name, [$this, 'resourceCallable']);
    }

    /**
     *
     * Attaches routes to a specific path prefix, and prefixes the attached
     * route names.
     *
     * @param string $name The prefix for all route names being
     *                           attached.
     *
     * @param string $path The prefix for all route paths being
     *                           attached.
     *
     * @param callable $callable A callable that uses the Router to add new
     *                           routes. Its signature is `function (\Aura\Router\Router $router)`; this
     *                           Router instance will be passed to the callable.
     *
     * @return null
     *
     */
    public function attach($path, $name, $callable)
    {
        // save current spec
        $spec = $this->getSpec();

        //var_dump($spec);

        // append to the name prefix, with delimiter if needed
        if (!class_exists($name)) {

            if ($this->namePrefix) {
                $this->namePrefix .= '.';
            }
            $this->namePrefix .= $name;

        } else {
            $this->namePrefix = $name;
        }

        // append to the path prefix
        $this->pathPrefix .= $path;

        // invoke the callable, passing this Collection as the only param
        call_user_func($callable, $this);

        // restore previous spec
        $this->setSpec($spec);
    }

    /**
     *
     * Sets the existing default route specification.
     *
     * @param array $spec The new default route specification.
     *
     * @return null
     *
     */
    protected function setSpec($spec)
    {
        foreach ($spec as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     *
     * Modifies the newly-added route to set an 'action' value from the route
     * name.
     *
     * @param Route $route The newly-added route.
     *
     * @return null
     *
     */
    protected function routeCallable(Route $route)
    {
        if ($route->name && !isset($route->values['action'])) {
            $route->addValues(['action' => $route->name]);
        }
    }

    /**
     *
     * Callable for `attachResource()` that adds resource routes.
     *
     * @param RouteCollection $router A RouteCollection, probably $this.
     *
     * @return null
     *
     */
    protected function resourceCallable(Collection $router)
    {
        // add 'id' and 'format' if not already defined
        $tokens = [];

        if (!isset($router->tokens['id'])) {
            $tokens['id'] = '(\d+[a-zA-Z0-9]{9})'; //(\d+)([a-zA-Z0-9-_]+)?so that we can have clean urls like 1230-title
        }
        if (!isset($router->tokens['format'])) {
            $tokens['format'] = '(\.[^/]+)?';
        }
        if ($tokens) {
            $router->addTokens($tokens);
        }

        // add the routes
        $router->addGet('{format}', 'index');
        $router->addGet('/{id}{format}', 'read');
        $router->addGet('/{id}/edit{format}', 'edit'); //if no id is set, return form for new
        //$router->addPost('/add{format}', 'add');
        $router->addDelete('/{id}/delete{format}', 'delete');
        //$router->addPost('/{id}/delete{format}', 'delete');
        $router->add('/create{format}', 'create');
        //$router->addPost('/{id}/update{format}', 'update');
        $router->addPatch('/{id}/update{format}', 'update');
        //$router->addPost('/{id}/replace{format}', 'replace');
        $router->addPut('/{id}/replace{format}', 'replace');
        $router->addOptions('', 'options');


    }

    public function addGet($uri, $name = null, $action = null)
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $name, $action);
    }

    public function addDelete($uri, $name = null, $action = null)
    {
        return $this->addRoute('DELETE', $uri, $name, $action);
    }

    public function addPost($uri, $name = null, $action = null)
    {
        return $this->addRoute('POST', $uri, $name, $action);
    }

    public function addPatch($uri, $name = null, $action = null)
    {
        return $this->addRoute('PATCH', $uri, $name, $action);
    }

    public function addPut($uri, $name = null, $action = null)
    {
        return $this->addRoute('PUT', $uri, $name, $action);
    }

    public function addOptions($uri, $name = null, $action = null)
    {
        return $this->addRoute('OPTIONS', $uri, $name, $action);
    }
} 