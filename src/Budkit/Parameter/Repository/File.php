<?php


namespace Budkit\Parameter\Repository;

use Budkit\Parameter\Loader;
use Budkit\Filesystem\File as _File;
use Budkit\Parameter\Repository\Parser;

class File extends _File implements Loader {

    protected $directory;
    protected $extension;
    protected $handlers;


    public function __construct($searchPath = DIRECTORY_SEPARATOR, $extension = ".ini"){
        $this->directory = $searchPath;
        $this->extension = $extension;

        //define handlers
        $this->handlers = [
            ".ini" => new Parser\Ini,
            ".xml" => new Parser\Xml
        ];
    }

    /**
     * Loads config from a specific namespace
     *
     * @param $env
     * @param string $group
     * @param string $namespace
     */
    public function load($environment, $namespace){

        $params = [];
        //evironments = /config/<environment>/namespace.ini[section].key
        $file  = $this->directory;
        $file .= !empty($environment) ? $environment.DIRECTORY_SEPARATOR : null ;
        $file .= $namespace;
        $file .= $this->extension;

        //Check that we have a file named namespace
        if (!array_key_exists($this->extension, $this->handlers)){
            throw new \Exception("config file handler for {$this->extension} does not exists");
        }

        $handler = $this->handlers[$this->extension];
        $params  = $handler->readParams($file);

        return $params;
    }

    /**
     * @param $group
     * @param string $namespace
     */
    public function hasSection($section, $namespace= ""){

    }

    /**
     * Check if repository has namespace
     *
     * @param $namespace
     */
    public function hasNamespace($namespace){

    }

    /**
     * Adds a namespace
     *
     * @param $namespace
     * @param array $items
     */
    public function addNamespace($namespace, $items = []){

    }


    public function getNamespaces(){

    }
}