<?php


namespace Budkit\Parameter;

interface Loader
{


    public function load($environment, $namespace);

    public function hasSection($section, $namespace = "");

    public function hasNamespace($namespace);

    public function addNamespace($namespace, $items = []);

    public function getNamespaces();

}