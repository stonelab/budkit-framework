<?php


namespace Budkit\Parameter\Repository;

use Budkit\Filesystem\File as _File;
use Budkit\Parameter\Loader;
use Budkit\Parameter\Repository\Parser;

class File extends _File implements Loader
{

    protected $directory;
    protected $extension;
    protected $handlers;


    public function __construct($searchPath = DIRECTORY_SEPARATOR, $extension = ".ini")
    {

        $this->directory = $searchPath;
        $this->extension = $extension;

        //define handlers
        $this->handlers = [
            ".ini" => new Parser\Ini($searchPath),
            ".xml" => new Parser\Xml($searchPath)
        ];
    }

    /**
     * Loads config from a specific namespace
     *
     * @param $env
     * @param string $group
     * @param string $namespace
     */
    public function load($environment, $namespace)
    {

        //evironments = /config/<environment>/namespace.ini[section].key
        $file = $this->directory;
        $file .= !empty($environment) ? $environment . DS : DS;
        $file .= $namespace;
        $file .= $this->extension;


        //Check that we have a file named namespace
        if (!array_key_exists($this->extension, $this->handlers)) {
            throw new \Exception("config file handler for {$this->extension} does not exists");
        }


        $handler = $this->handlers[$this->extension];
        $params = $handler->readParams($file);


        return $params;
    }

    /**
     * @param $group
     * @param string $namespace
     */
    public function hasSection($section, $namespace = "")
    {

    }


    public function saveParams($params, $environment = "")
    {

        $handler = $this->handlers[$this->extension];

        return $handler->saveParams($params, $environment);
    }

    /**
     * Check if repository has namespace
     *
     * @param $namespace
     */
    public function hasNamespace($namespace)
    {

    }

    /**
     * Adds a namespace
     *
     * @param $namespace
     * @param array $items
     */
    public function addNamespace($namespace, $items = [])
    {

    }


    public function getNamespaces()
    {

    }
}