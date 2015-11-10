<?php

namespace Budkit\View\Engine;

use Budkit\Protocol\Response;
use Budkit\View\Format;

class Json implements Format
{

    protected $data;

    protected $response;

    public function compile($viewpath, array $data = [])
    {

        //We need to sniff for stuff in the output array we don't want to send to the user
        return json_encode($this->response->getAllParameters());
    }


    public function  addLayoutSearchPaths(array $searchPaths = [])
    {

    }


    public function addLayoutData(array $layoutData)
    {


    }

    public function setResponse(Response $response){
        $this->response = $response;
    }
}