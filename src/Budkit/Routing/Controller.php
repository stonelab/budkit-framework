<?php

namespace Budkit\Routing;

use Budkit\Application\Support\Mock;
use Budkit\Application\Support\Mockable;
use Budkit\Dependency\Container as Application;
use Budkit\Event\Event;
use Budkit\Event\Listener;
use Budkit\View\Display as View;
use Budkit\View\Engine;
use Exception;
use ReflectionMethod;

//not using platform here bc cli also uses controllers

/**
 * Class Controller
 *
 * @package Budkit\Routing
 */
class Controller implements Mockable, Listener
{

    use Mock;

    public $autoRender = false;
    protected $observer;
    protected $request;
    protected $response;
    protected $application;
    protected $view;
    protected $config;
    private $rendered = false;

    public function __construct(Application $application)
    {

        $this->observer = $application->observer;
        $this->response = $application->response;
        $this->request = $application->request;
        $this->application = $application;
        $this->view = $this->getView();
        $this->config = $application->config;

        //Attach controllers to the observer;
        $this->observer->attach($this);
    }

    public function getView()
    {

        $handler = $this->application->createInstance("viewengine", [$this->response]);

        return $this->view =
            ($this->view instanceof View) ? $this->view : new View([], $this->response, $this->getHandler());
    }

    /**
     * Sets the view for this controller action
     *
     * @param string $view
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function setView($view)
    {

        if (class_exists($view)) {
            //if we already have a default view
            $values = [];

            //grab all its parameters and store in parameters
            if (isset($this->view) && $this->view instanceof View) {

                $values = $this->view->getDataArray();
                $layout = $this->view->getLayout();

                //Load the newView;
                $this->view = $this->loadView($view, $values);

                if (!empty($layout)) {
                    $this->view->setLayout($layout);
                }
            }

            return $this;

        } else if (is_string($view)) {

            $this->response->addContent($view); //so that $this->view("Hi There");  will output Hi There;

            return $this;

        }

        return false; //no view set;
    }

    private function getHandler()
    {

        if (isset($this->application['viewengine'])) {
            return $this->application->viewengine;
        }

        $handler = $this->application->createInstance("viewengine", [$this->response]);

        return $this->application->shareInstance($handler, "viewengine");

    }

    public function definition()
    {
        return ['Controller.shutdown' => 'autoRender'];
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Initialise controller events;
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function initialize()
    {
        $this->observer->trigger(new Event('Controller.initialize', $this));
    }

    public function autoRender(Event $onShutDown)
    {

        if ($this->rendered) {
            return true;
        }

        return $this->render($onShutDown->get("object")->getView());

    }

    /**
     * Renders the Controller->Response;
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function render(View $view = null)
    {

        if ($this->rendered) {
            return true;
        }


        $onRender = new Event('Controller.beforeRender', $this);

        $this->observer->trigger($onRender);
        if ($onRender->isStopped()) {
            $this->autoRender = false;

            return $this->response;
        }

        //controllers only know about one view.
        //Every action uses a single view;
        //views can be set with $this->setView("") or $this->display(""); //or can be set on render;

        //If we are setting a really late view;
        $view = !is_null($view) ? $view : $this->getView();

        $this->response->addContent($view->render());

        $this->rendered = true;

        return true;
    }

    public function display($view)
    {
        if (!$this->setView($view)) {
            throw Exception("Could not display {$view}");
        }

        return $this;
    }

    /**
     * Checks that the method is callable;
     *
     * @param string $action
     * @param Route $route
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function invokeAction($action, $params = [])
    {

        //var_dump($this->request->getAttributes());

        //Will throw an exception if the method does not exists;
        $method = new ReflectionMethod($this, $action);

        if (!$method->isPublic()) {
            throw new Exception('Attempting to call a private method');
        }

        return $method->invokeArgs($this, $params);

    }

    public function shutdown()
    {
        $this->observer->trigger(new Event('Controller.shutdown', $this));
    }

    /**
     * Loads a view from classname;
     *
     * @param string $view
     *
     * @return void
     * @author Livingstone Fultang
     */
    protected function loadView($view, $values = [])
    {


        if (isset($this->application[$view])) {
            return $this->application[$view];
        }

        $instance = $this->application->createInstance($view, [$values, $this->response, $this->getHandler()]);

        //Otherwise return an instance of Controller;
        return $this->application->shareInstance($instance, $view);
    }

}