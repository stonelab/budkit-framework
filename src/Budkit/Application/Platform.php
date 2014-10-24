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
    /**
     * Construct the platform
     *
     * @param Request $request
     */
    public function __construct() {
        parent::__construct();

        //Add additional aliases for Required Classes;
        //@TODO detect protocol before adding the classes here;
        $this->createAlias(
            $aliases = [
                'request'  => 'Budkit\Protocol\Http\Request',
                'response' => 'Budkit\Protocol\Http\Response'
            ]
        );

        $this->initialize(); //boots all registered plugins;
    }

    //Execute the request and return an response via protocol interface
    //e.g this->exchange request to get a response;

    public function execute(Request $request = null) {
        $request = $request ?: $this->createRequest(); //shorthand teneray operator

        //The global dispatcher
        $this->shareInstance($this->createInstance('Budkit\Routing\Dispatcher'), 'dispatcher');

        $this->dispatcher->dispatch($request, $this->response);

    }

    protected function createRequest() {
        //Create a request using global vars if available;
        return $this->shareInstance($this->request->createFromGlobal(), "request"); //no need to share;
    }

} 