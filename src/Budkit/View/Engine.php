<?php

namespace Budkit\View;

use Budkit\Protocol\Response;
use Budkit\View\Engine as Handler;
use Budkit\View\Format;
use Budkit\Dependency\Container;

class Engine{
	
	protected $handler = null;
	protected $response;
	protected $container;
	
	public function __construct(Response $response, Container $container){
		
		$this->response = $response;
		$this->container = $container;
	}
	
	public function getHandler(){
		
		$format 	 = $this->response->getContentType();
		$engineClass = 'Budkit\View\Engine\\'.ucfirst($format);
		
		if(isset($this->container[$engineClass])){
			return $this->container[$engineClass]; 
		}
		
		if(class_exists($engineClass)){
			$engine = $this->container->createInstance( $engineClass );
			if($engine instanceof Format)
				$this->handler = $engine;
		}else{
			$this->handler = new Handler\Html;
		}
		
		return $this->handler;
	}
	
}