<?php

namespace Budkit\View\Engine;

use Budkit\View\Format;
use Budkit\View\Layout\Compiler;
use Budkit\FileSystem\File;

class Json implements Format{
	
	public function __construct(){}
	
	public function compile($viewpath, array $data = array()){
		
		echo "copiling json";
		//only data that would otherwise be displayed template file will be passed as json output;
		//var_dump($viewpath, $data, "{j:s,o:n}");
		
	}
		
}