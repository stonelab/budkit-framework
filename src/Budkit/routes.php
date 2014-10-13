<?php

//$app->route->add("/callme", "callme")
//	->setValues(array(
//		"action"=>function($response, $params = null)use($app){
//
//			//var_dump($params);
//
//			$response->setContentType("json");
//
//			return $response->addContent("{j:s,o:n}");
//
//		})
//	);
//
////Grouping routes under a prefix;
//Route::attach("/blog", "blog", function($route){
//	$route->setTokens(array(
//	   'id'		=> '\d+',
//	   'format'	=>'(\.json|\.atom|\.html)'
//	));
//   //subroutes
//   $route->add( '{format}','browse');
//   $route->add('/{id}{format}', "read");
//   $route->add('/{id}/edit{format}', "edit");
//
//});