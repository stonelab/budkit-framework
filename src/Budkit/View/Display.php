<?php


namespace Budkit\View;

use Budkit\Parameter\Factory as Parameters;
use Budkit\Dependency\Container as Application; //not using platform here bc cli also uses controllers
use Budkit\Application\Support\Mockable;
use Budkit\Application\Support\Mock;

class Display extends Parameters implements Mockable{
	
	use Mock;
	
	public function __construct(array $data = array() ){
		parent::__construct("display", $data );
	}
	
	
	public function render($layout = null){
		
		//Determine Rending Format;
		
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