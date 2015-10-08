<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 21/06/2014
 * Time: 17:22
 */

namespace Budkit\Application;


use Budkit\Protocol\Request;

/**
 * The Base Application Class
 *
 * Class Platform
 *
 * @package Budkit\Application
 */
class Platform extends Support\Application {

    protected $paths;
    /**
     * Construct the platform
     *
     * @param Request $request
     */
    public function __construct() {

        parent::__construct();

        //Add additional aliases for Required Classes;
        //@TODO detect protocol before adding the classes here;
//        $this->createAlias(
//            $aliases = [
//        'request'  => 'Budkit\Protocol\Http\Request',
//            'response' => 'Budkit\Protocol\Http\Response',
//            ]
//        );

        $this->shareInstance($this->createRequestFromGlobals(), "request"); //no need to share;
        //$this->shareInstance($this->createInstance("input", [ $request ]), "input");

        //Datastore and Session
        //@TODO load datbase drivers from configuration

        $this->initialize(); //boots all registered plugins;
    }

    //Execute the request and return an response via protocol interface
    //e.g this->exchange request to get a response;

    public function execute(Request $request = null) {

        $request = $request ?: $this->request; //shorthand teneray operato

        $this->dispatcher->dispatch($request, $this->response);

    }


    public function createRequestFromGlobals()
    {
        $_ATTRIBUTES = array_merge([], $_ENV, isset($_SESSION) ? $_SESSION : []);

        $SERVER = $_SERVER;

        if(isset($_POST["_method"]) ){
            //Hack to allow PATCH, DELETE, OPTIONS etc!
            $SERVER['REQUEST_METHOD'] = $_POST["_method"];
        }

        return new \Budkit\Protocol\Http\Request( $_GET, $_POST, $_ATTRIBUTES, $_COOKIE, $_FILES, $SERVER );
    } //abstract method

} 