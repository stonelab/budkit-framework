<?php

namespace Budkit\View\Engine;

use Budkit\View\Format;

class Json implements Format
{

    protected $data;

    public function __construct()
    {


    }

    public function compile($viewpath, array $data = [])
    {

        echo "copiling json";
        //only data that would otherwise be displayed template file will be passed as json output;
        //var_dump($viewpath, $data, "{j:s,o:n}");

    }


    public function  addLayoutSearchPaths(array $searchPaths = [])
    {

    }


    public function addLayoutData(array $layoutData)
    {


    }
}