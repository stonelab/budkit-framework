<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 27/06/2014
 * Time: 03:59
 */

namespace Budkit\Protocol;

interface Response {

    public function addHeader($key, $value = ""); //sets the header;
    public function addContent($content = null); //sets the content;
    public function setStatusCode($code);
    public function setProtocolVersion($version );
    public function getProtocolVersion();
    public function getStatusCode();
    public function getHeader($key, $default = '');
    public function getContent(); //gets the content;
    public function send($content = null); //send the response

} 