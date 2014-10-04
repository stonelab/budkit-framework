<?php


namespace Budkit\View;

use Budkit\Parameter\Factory as Parameters;
use Budkit\Protocol\Response;
use Budkit\Application\Support\Mockable;
use Budkit\Application\Support\Mock;
use Budkit\View\Layout\Parser;


class Display extends Parameters implements Mockable{
	
	use Mock;
	
	protected $rendered = false;
	
	protected $response;
	
	protected $layout = null;
	
	
	public function __construct(array $data = array(), Response $response){
		
		$this->response = $response;
		
		parent::__construct("display", $data );
	}
	
	
	public function render($layout = null){
		
		if($this->rendered) return true;
		//Determine Rending Format;
		$format = $this->response->getContentType();
		
		echo $format;
		
		//var_dump($this->response);
		
		$this->rendered = true;
		
	}
	
	
	protected function setDataArray(array $values){
		foreach($data as $key=>$value){
			$this->setData($key, $value);
		}
		return $this;
	}
	
	
	protected function getDataArray(){
		$this->view->getAllParameters();
	}
	
	protected function setData($key, $value = ''){
		return $this->setParameter($key, $value);
	}
	
	protected function getData($key){
		return $this->getParameter($key);
	}
	
}