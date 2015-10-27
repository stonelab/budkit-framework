<?php


namespace Budkit\Parameter\Repository;


use Budkit\Parameter\Loader;

class Database implements Loader
{
    public function load($environment, $namespace){}

    public function hasSection($section, $namespace = ""){}

    public function hasNamespace($namespace){}

    public function addNamespace($namespace, $items = []){}

    public function getNamespaces(){}
}