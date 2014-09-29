<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 27/06/2014
 * Time: 03:59
 */

namespace Budkit\Protocol\Http;

use Budkit\Protocol;
use Budkit\Protocol\Http\Headers;

class Response implements Protocol\Response {

    /**
     * All HTTP status codes are defined in this trait;
     */
    use Codes;

    /**
     * @var \Symfony\Component\HttpFoundation\ResponseHeaderBag
     */
    public $headers;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $statusText;

    /**
     * @var string
     */
    protected $charset;


    public function __construct( Headers $headers, $content = '', $status = 200){

        $this->headers = $headers;

        //print_R($this->headers);
        //headers must be an instance of the headers class;
    }

    public function setHeaders(){
        //set multiple headers;
    }
    public function setHeader(){}
    public function getHeader(){}

    public function setStatusCode(){}
    public function getStatusCode(){}

    public function setCharset(){}
    public function getCharset(){}

    protected function sendHeaders(){} //sends the headers
    protected function sendContent(){} //sends the content;

    public function setContent(){}
    public function getContent(){}


    public function send(){

        $this->sendHeaders();
        $this->sendContent();

        return $this;
    }


    public function make(Protocol\Request $request){}


} 