<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 04/07/2014
 * Time: 20:05
 */

namespace Budkit\Routing;

use Budkit\Dependency\Container;
use Budkit\Event;
use Budkit\Event\Listener;
use Budkit\Event\Observer;
use Budkit\Protocol\Request;
use Budkit\Protocol\Response;
use Budkit\Protocol\Uri;
use Closure;
use Exception;

;


class Dispatcher implements Listener
{


    protected $observer;
    protected $router;
    protected $application;

    /**
     * Constructs the Dispatcher class
     * and attaches itself to the observer;
     *
     * @param Observer $observer
     *
     * @author Livingstone Fultang
     */
    public function __construct(Observer $observer, Router $router, Container $application)
    {

        $this->observer = $observer;
        $this->router = $router;
        $this->application = $application;

        $this->observer->attach($this);

        //var_dump($this->observer->getListeners('Dispatcher.beforeDispatch')  );

        //@TODO load additional listeners from config
    }


    public function definition()
    {

        return ['Dispatcher.beforeDispatch' => 'parseRoute'];

    }

    public function getObserver()
    {
        return $this->observer;
    }

    /**
     * Recieves the beforeDispatchEvent,
     * Routes the applicaition and gets all route params
     * Stores Route params in the beforeDispatch Event results
     *
     * @param string $beforeDispatch Event
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function parseRoute($beforeDispatch)
    {

        //echo '1. Check the request; <br/>2. $response =  $this->sync(); //to get a synchronous response; <br/>3. $response->send();<br />';

        //var_dump($this->router);
        //var_dump($beforeDispatch->get('data'));
        $request = $beforeDispatch->getData('request');
        $route = $this->router->matchToRoute($request);

        if (!($route instanceof Route)) {
            throw new Exception("A valid route could not be determined");
        }
        $format = "html";
        //clean up format
        if (isset($route->params['format'])) {
            $format = str_replace([".", " ", "_", "-"], "", $route->params['format']);

        }
        $route->setParam("format", $format);

        $request->setAttributes($route->params);

        //Store the route in the event data
        $beforeDispatch->data['route'] = $route;

    }


    public function dispatch(Request $request, Response $response = null, $params = [])
    {

        //create an event;
        $beforeDispatch = new Event\Event('Dispatcher.beforeDispatch', $this, compact('request', 'response', 'params'));
        $this->observer->trigger($beforeDispatch);


        //For microframework routes that use lambdas, just return a response object;
        if ($beforeDispatch->getResult() instanceof Response) {
            $beforeDispatch->getResult()->send();

            return;
        }

        //create an event;
        $afterRouteMatch = new Event\Event('Dispatcher.afterRouteMatch', $this, compact('request', 'response'));
        $this->observer->trigger($afterRouteMatch);


        $controller = $this->resolveController($request);


        if (!$controller || !is_callable($controller)) {
            throw new Exception("Controller is not callable");
        }


        //print_r($request);

        $params = $request->getAttributes();
        $params = $params->getAllParameters(); //from parameter factory;

        unset($params['action']); //remove the action;

        //Can we get the route?
        //$route  = $beforeDispatch->getData('route');

        //Are there any left over alerts?

        //If we are using lambdas;
        if ($controller instanceof Closure) {
            $response = call_user_func_array($controller, [$response, $params]);
        } else {
            list($class, $method) = $controller;
            $response = $this->invoke($class, $method, $params);

            //print_R($response->getDataArray() );  die;
        }

        // if (isset($request->params['return'])) {
        // 	return $response->body();
        // }

        //create an event;
        $afterDispatch = new Event\Event('Dispatcher.afterDispatch', $this, compact('request', 'response'));
        $this->observer->trigger($afterDispatch);

        if (isset($afterDispatch->data['response'])) {
            $afterDispatch->data['response']->send();
        }


    }

    protected function resolveController(Request $request)
    {

        $controller = false;
        $attributes = $request->getAttributes();


        //print_r($attributes);

        if (!empty($attributes['action'])) {
            //Note that this will be true if action is a valid controller or lambda;
            $controller = $attributes['action'];


            if (is_callable($controller)) {
                //If the controller is not a function;
                if (!($controller instanceof Closure)) {
                    return $this->getController($controller);
                }
            }


            //If its not callable and is string;
            if (is_string($attributes['action'])) {


                if (isset($attributes['controller'])) {


                    //for when /{controller}/{action}{/param1,param2,param3} is used
                    $class = $this->sanitize($attributes['controller']);
                    $method = $this->sanitize($attributes['action']);


                } else {
                    //for when no action is give, uses route name
                    //if route is in group then most likely it has a name like Prefix.name
                    $action = explode(".", $attributes['action']);


                    $class = $this->sanitize(ucfirst($action[0]));//controller;
                    $method = $this->sanitize(isset($action[1]) ? $action[1] : "index");  //method;

                }
                //Does the action exists in the actionController?
                if (!method_exists($class, $method)) {
                    throw new Exception("Method '{$method}' does not exists in Controller '{$class}'");

                    return false;
                }


                $controller = [$this->getController($class), $method];


                if (is_callable($controller)) {
                    return $controller;
                }
            }
        }

        return $controller;
    }

    protected function getController($class)
    {


        if (isset($this->application[$class])) {
            return $this->application[$class];
        }

        //Otherwise return an instance of Controller;
        return $this->application->shareInstance($this->application->createInstance($class), $class);

    }

    protected function sanitize($string, $notallowed = [".", " ", "_", "-"])
    {
        return str_replace($notallowed, "", $string);
    }


    protected function invoke(Controller $controller, $method = "index", $params = [])
    {

        $controller->initialize();

        //Reset old alerts
        $oldAlerts = $this->application->session->get("alerts");
        if (!empty($oldAlerts)) {
            $controller->resetStoredResponseVars(["alerts" => $oldAlerts]);
            $this->application->session->remove("alerts");
        }


        $response = $controller->getResponse();
        $render = true;

        $result = $controller->invokeAction($method, $params);

        if ($result instanceof Response) {
            $render = false;
            $response = $result;
        }

        if ($render && $controller->autoRender) {
            $response = $controller->render();
        } elseif (!($result instanceof Response) && $response->getContent() === null) {
            $response->addContent($result);
        }

        $controller->shutdown();

        return $response;
    }

    /**
     * Aborts the execution of the script
     *
     * @return void
     */
    final public function abort()
    {
        exit();
    }


    /**
     * Executes post dispatch redirect
     *
     * @param type $url
     * @param type $code
     * @param type $message
     */
    public function redirect($url, $code = HTTP_FOUND, $message = "Moved Permanently", $alerts = [])
    {

        $response = $this->application->response;
        $uri = $this->application->createInstance(Uri::class, [$this->application->request]);

        //Before we redirect, if there are any alerts in the response,
        //Exceptional: Store alerts for future display
        if (!empty($alerts)) {
            $session = $this->application->session;

            //$session->unlock("default"); //unlock the default namespace
            $session->set("alerts", $alerts, "default");
            //$session->update( $session->getId() );
        }

        $response->setStatusCode($code);
        $response->setStatusMessage($message);
        $response->addHeader("Location", $uri->internalize($url));

        $response->sendRedirect();

        $this->abort();
    }

}