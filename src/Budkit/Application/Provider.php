<?php

namespace Budkit\Application;


use Budkit\Application\Support\Service;
use Budkit\Dependency\Container;

class Provider implements Service
{

    protected $application;

    public function __construct(Container $application)
    {
        $this->application = $application;
    }

    public static function  getPackageDir()
    {
        return __DIR__ . "/";
    }

    public function onRegister()
    {

        //Add Roues
        ////Grouping routes under a prefix;
        $application = $this->application;
        \Route::add("/", "home", function($response, $params = null) use ($application){

            //$response->setContentType("json");
//
			return $response->addContent("<pre>Hey Buddy! Welcome to Budkit ;)</pre>");

        });
    }

    public function definition()
    {
        return ["app.register" => "onRegister"];
    }
}