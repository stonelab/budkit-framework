<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 27/06/2014
 * Time: 03:59
 */

namespace Budkit\Protocol;

interface Response {

    public function setHeader(); //sets the header;
    public function setContent(); //sets the content;
    public function setStatusCode();
    public function getStatusCode();
    public function getHeader();
    public function getContent(); //gets the content;
    public function send(); //send the response

} 