<?php

namespace Budkit\View\Engine;

use Budkit\Protocol\Response;
use Budkit\View\Format;
use Budkit\View\Layout\Compiler;
use Budkit\View\Layout\Loader;

class Html implements Format
{

    protected $loader;
    protected $compiler;

    public function __construct(Loader $loader, Compiler $compiler)
    {
        $this->loader = $loader;
        $this->compiler = $compiler;

    }


    /**
     * Tells the display class that we need private data;
     * @return bool
     */
    public function needsPrivateData(){

        return true;

    }

    public function compile($viewpath, array $data = [])
    {

        //for now just import the file;
        return $this->compiler->execute($this->loader->find($viewpath), $data);

    }

    public function  addLayoutSearchPaths(array $searchPaths = [])
    {

        $this->loader->addSearchPaths($searchPaths);

    }

    public function addLayoutData(array $layoutData)
    {

        $this->loader->addData($layoutData);

    }


    public function setResponse(Response $response){

    }

}