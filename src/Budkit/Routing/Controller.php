<?php

namespace Budkit\Routing;

use ReflectionMethod;
use Exception;
use Budkit\Event\Event;
use Budkit\Event\Listener;
use Budkit\Event\Observer;
use Budkit\Protocol\Request;
use Budkit\Protocol\Response;
use Budkit\View\Display as View;
use Budkit\Parameter\Factory as Parameters;
use Budkit\Dependency\Container as Application; //not using platform here bc cli also uses controllers
use Budkit\Application\Support\Mockable;
use Budkit\Application\Support\Mock;

class Controller implements Mockable, Listener{
	
	 use Mock;
	
	 protected $observer;
	 protected $request;
	 protected $response;
	 protected $application;
	 protected $view;
	 private   $rendered = false; 
	 public    $autoRender = false;
	 
	 
	public function __construct(Application $application){
		
		$this->observer 	= $application->observer;
		$this->response 	= $application->response;
		$this->request 		= $application->request;
		$this->application 	= $application;
		$this->view 		= new View(array(), $this->response );
		
		//Attach controllers to the observer;
		$this->observer->attach($this);
	}
 
	public function definition(){
		return array(
			'Controller.shutdown'=>'autoRender'
		);
	}
	
 	public function getRequest(){
 		return $this->request;
 	}
	
	
	public function getResponse(){
		return $this->response;
	}

	
 	/**
 	 * Initialise controller events;
 	 *
 	 * @return void
 	 * @author Livingstone Fultang
 	 */
	public function initialize() {
		$this->observer->trigger(new Event('Controller.initialize', $this));
	}
	
	
	public function autoRender(Event $onShutDown ){
		
		if($this->rendered) return true;	
		return $this->render($onShutDown->get("object")->getView());
		
	}

	/**
	 * Renders the Controller->Response;
	 *
	 * @return void
	 * @author Livingstone Fultang
	 */
	public function render(View $view = null){
		
		if($this->rendered) return true;
		
		
		$onRender = new Event('Controller.beforeRender', $this);
		
		$this->observer->trigger($onRender);
		if ($onRender->isStopped()) {
			$this->autoRender = false;
			return $this->response;
		}
		
		$view = !is_null($view) ? $this->getView() : $view ;
		
		
		//var_dump($this->response);
		
		$this->response->addContent( $view->render() );
		
		//controllers only know about view. 
		//Every action uses a single view;
		//views can be set with $this->setView("") or $this->display(""); //or can be set on render;
		
		$this->rendered = true;
		
		return true;
	}
	
	
	public function display($view){
		return $this->setView($view);
	}
	
	/**
	 * Sets the view for this controller action
	 *
	 * @param string $view 
	 * @return void
	 * @author Livingstone Fultang
	 */
	public function setView( $view ){
		
		if(is_callable($view)){
			//if we already have a default view
			$values = array();
			//grab all its parameters and store in parameters
			if(isset($this->view) && $this->view instanceof View){
				$values = $this->view->getValues();
				$this->view = $this->loadView( $view, $values );
			}	
			return true;
		}
		
		//If the controller is not a function;
		if(is_string($view)){
			$this->response->addContent( $view ); //so that $this->view("Hi There");  will output Hi There;
			return true;
		}
		
		return false; //no view set;
	}
	
	
	public function getView(){
		return $this->view = ($this->view instanceof View) ? $this->view : new View(array(), $this->response); 
	}
	
	/**
	 * Loads a view from classname;
	 *
	 * @param string $view 
	 * @return void
	 * @author Livingstone Fultang
	 */
	protected function loadView( $view , $values = array() ){
		
		if(isset($this->application[$view])) 
			return $this->application[$view];
		
		//Otherwise return an instance of Controller;
		return $this->application->shareInstance( $this->application->createInstance($view, array($values, $this->response) ), $view);
	}
	
		
	/**
	 * Checks that the method is callable;
	 *
	 * @param string $action 
	 * @param Route $route 
	 * @return void
	 * @author Livingstone Fultang
	 */
	public function invokeAction($action, $params = array() ) {
		
		//var_dump($this->request->getAttributes());
		
		//Will throw an exception if the method does not exists;
		$method = new ReflectionMethod($this, $action);

		if (!$method->isPublic() ) {	
			throw new Exception('Attempting to call a private method');
		}
		
		return $method->invokeArgs($this, $params);
		
	}
	
	
	public function shutdown() {
		$this->observer->trigger(new Event('Controller.shutdown', $this));
	}
	 
}