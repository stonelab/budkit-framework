<?php
    /**
     * Created by PhpStorm.
     * User: livingstonefultang
     * Date: 04/07/2014
     * Time: 20:05
     */

    namespace Budkit\Routing;

    use Budkit\Event;
    use Closure;
    use Exception;
    use Budkit\Protocol\Request;
    use Budkit\Protocol\Response;
    use Budkit\Routing\Route;
    use Budkit\Routing\Router;
    use Budkit\Dependency\Container;


    class Dispatcher implements Event\Listener {


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
        public function __construct(Event\Observer $observer, Container $application) {

            $this->observer = $observer;
            $this->router = $application->router;
            $this->application = $application;
            $this->observer->attach($this);

            //@TODO load additional listeners from config
        }


        public function definition() {
            return array('Dispatcher.beforeDispatch' => 'parseRoute');
        }

        public function getObserver() {
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
        public function parseRoute($beforeDispatch) {

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
                $format = str_replace(array(".", " ", "_", "-"), "", $route->params['format']);
            }
            $route->setParam("format", $format);

            $request->setAttributes($route->params);

            //Store the route in the event data
            $beforeDispatch->data['route'] = $route;

        }


        public function dispatch(Request $request, Response $response = null, $params = array()) {

            //create an event;
            $beforeDispatch = new Event\Event('Dispatcher.beforeDispatch', $this, compact('request', 'response', 'params'));
            $this->observer->trigger($beforeDispatch);

            //Can we get the route?
            //$route  = $beforeDispatch->getData('route');

            //For microframework routes that use lambdas, just return a response object;
            if ($beforeDispatch->getResult() instanceof Response) {
                $beforeDispatch->getResult()->send();

                return;
            }


            $controller = $this->resolveController($request);


            if (!$controller || !is_callable($controller)) {
                throw new Exception("Controller is not callable");
            }

            $params = $request->getAttributes();
            $params = $params->getAllParameters(); //from parameter factory;

            unset($params['action']); //remove the action;

            //If we are using lambdas;
            if ($controller instanceof Closure) {
                $response = call_user_func_array($controller, array($response, $params));
            }
            else {
                list($class, $method) = $controller;
                $response = $this->invoke($class, $method, $params);
            }

            // if (isset($request->params['return'])) {
            // 	return $response->body();
            // }

            //create an event;
            $afterDispatch = new Event\Event('Dispatcher.afterDispatch', $this, compact('request', 'response'));
            $this->observer->trigger($afterDispatch);

            if (isset($afterDispatch->data['response']))
                $afterDispatch->data['response']->send();


        }

        protected function resolveController(Request $request) {

            $controller = false;
            $attributes = $request->getAttributes();

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
                    }
                    else {
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

                    $controller = array($this->getController($class), $method);

                    if (is_callable($controller)) {
                        return $controller;
                    }
                }
            }

            return $controller;
        }

        protected function getController($class) {

            if (isset($this->application[ $class ]))
                return $this->application[ $class ];

            //Otherwise return an instance of Controller;
            return $this->application->shareInstance($this->application->createInstance($class), $class);

        }

        protected function sanitize($string, $notallowed = array(".", " ", "_", "-")) {
            return str_replace($notallowed, "", $string);
        }

        protected function invoke(Controller $controller, $method = "index", $params = array()) {

            $controller->initialize();

            $response = $controller->getResponse();
            $render = true;

            $result = $controller->invokeAction($method, $params);

            if ($result instanceof Response) {
                $render = false;
                $response = $result;
            }

            if ($render && $controller->autoRender) {
                $response = $controller->render();
            }
            elseif (!($result instanceof Response) && $response->getContent() === null) {
                $response->addContent($result);
            }

            $controller->shutdown();

            return $response;
        }

    }