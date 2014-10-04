<?php 
namespace Budkit\Event;

interface Listener{
	
	/**
	 * Define all events and their respective callbacks
	 * E.g return array('onDispatch'=>'parseRoute'); 
	 *
	 * When string callbacks are given such as parseRoute it must be a valid
	 * method in the current object, alternatively you can pass a callable
	 * E.g return array('onDispatch'=>function(){});
	 *
	 * Multiple callables can also be passed for a single event, e.g
	 * E.g return array('onDispatch'=>array('parseRoute','checkParams',function(){}))
	 *
	 * @return array 
	 * @author Livingstone Fultang
	 */
	public function definition();
		
}