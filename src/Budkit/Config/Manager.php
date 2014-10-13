<?php

namespace Budkit\Config;

use ArrayAccess;
use Budkit\Config\Loader;

class Manager implements ArrayAccess {

    protected $loader;
    protected $environment;
    protected $items = [];
    protected $packages = [];

    public function __construct(Loader $loader, $environment = null) {
        $this->loader = $loader;
        $this->environment = $environment;
    }

    public function has($key) {
        $default = microtime(true);

        return $this->get($key, $default) !== $default;
    }

    public function hasGroup($key) {
        list($namespace, $group, $item) = $this->parseKey($key);

        return $this->loader->exists($group, $namespace);
    }

    public function get($key, $default = null) {
        list($namespace, $group, $item) = $this->parseKey($key);

        // Configuration items are actually keyed by "collection", which is simply a
        // combination of each namespace and groups, which allows a unique way to
        // identify the arrays of configuration items for the particular files.
        $collection = $this->getCollection($group, $namespace);

        $this->load($group, $namespace, $collection);

        return array_get($this->items[ $collection ], $item, $default);
    }

    public function set($key, $value) {
        list($namespace, $group, $item) = $this->parseKey($key);

        $collection = $this->getCollection($group, $namespace);

        // We'll need to go ahead and lazy load each configuration groups even when
        // we're just setting a configuration item so that the set item does not
        // get overwritten if a different item in the group is requested later.
        $this->load($group, $namespace, $collection);

        if (is_null($item)) {
            $this->items[ $collection ] = $value;
        }
        else {
            array_set($this->items[ $collection ], $item, $value);
        }
    }

    protected function load($group, $namespace, $collection) {
        $env = $this->environment;

        // If we've already loaded this collection, we will just bail out since we do
        // not want to load it again. Once items are loaded a first time they will
        // stay kept in memory within this class and not loaded from disk again.
        if (isset($this->items[ $collection ])) {
            return;
        }

        $items = $this->loader->load($env, $group, $namespace);

        $this->items[ $collection ] = $items;
    }

    protected function getCollection($group, $namespace = null) {
        $namespace = $namespace ?: '*';

        return $namespace . '::' . $group;
    }

    public function addNamespace($namespace, $hint) {
        $this->loader->addNamespace($namespace, $hint);
    }

    public function getNamespaces() {
        return $this->loader->getNamespaces();
    }

    public function getLoader() {
        return $this->loader;
    }

    public function setLoader(Loader $loader) {
        $this->loader = $loader;
    }

    public function getEnvironment() {
        return $this->environment;
    }

}