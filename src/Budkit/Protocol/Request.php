<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 27/06/2014
 * Time: 03:58
 */

namespace Budkit\Protocol;


interface Request {

    public function send(Request $request = null); //ability to send a request;

    public function getResponse(); //ability to get a response following a send;

    public function getProtocol(); //quick means to determine which protocol we are dealing with;

} 