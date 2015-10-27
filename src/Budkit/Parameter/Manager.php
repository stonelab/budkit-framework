<?php

namespace Budkit\Parameter;

use ArrayAccess;
use Budkit\Application\Support\Mock;
use Budkit\Application\Support\Mockable;

class Manager implements ArrayAccess, Mockable
{

    const SEPARATOR = '/[:\.]/';

    use Utility;
    use Mock;

    protected $repository;
    protected $environment;
    protected $loaded = [];


    public function __construct(Loader $loader, $environment = null, $parameters = [])
    {

        $this->repository = $loader;
        $this->environment = $environment;


        $this->addParameters($parameters);

    }


    /**
     * @param string $path
     * @param string $default
     * @return mixed
     */
    public function get($path, $default = null, $forceReload = false)
    {
        //We need keys to have at least a namespace a section and a key i.e
        //namespace.section.key
        $dotparts = explode(".", $path);
        $dotcount = count($dotparts);

        if ($dotcount < 2) return $default;

        //@TODO in PHP7 this will have to be the reverse
        list($namespace, $section, $key) = array_pad($dotparts, 3, "");

        //check that namespace has been loaded
        if ($forceReload || !array_key_exists($namespace, $this->loaded)) {
            $this->load($namespace);
        }

        $array = $this->parameters;
        $keys = $this->explode($path);


        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $array = $array[$key];
            } else {
                return $default;
            }
        }
        return $array;
    }

    /**
     * @param string $path
     * @param mixed $value
     */
    public function set($path, $value)
    {
        if (!empty($path)) {
            $at = &$this->parameters;
            $keys = $this->explode($path);
            while (count($keys) > 0) {
                if (count($keys) === 1) {
                    if (is_array($at)) {
                        $at[array_shift($keys)] = $value;
                    } else {
                        throw new \RuntimeException("Can not set value at this path ($path) because is not array.");
                    }
                } else {
                    $key = array_shift($keys);
                    if (!isset($at[$key])) {
                        $at[$key] = array();
                    }
                    $at = &$at[$key];
                }
            }
        } else {
            $this->parameters = $value;
        }
    }

    public function mergeParams($namespace, $params = [])
    {

        return $this->load($namespace, $params);

    }

    /**
     * @param $path
     * @param array $values
     */
    public function add($path, array $values)
    {
        $get = (array)$this->get($path);
        $this->set($path, $this->arrayMergeRecursiveDistinct($get, $values));
    }

    /**
     * @param string $path
     * @return bool
     */
    public function have($path)
    {
        $keys = $this->explode($path);
        $array = $this->parameters;
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $array = $array[$key];
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $values
     */
    public function setValues($values)
    {
        $this->parameters = $values;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->parameters;
    }

    protected function explode($path)
    {
        return preg_split(self::SEPARATOR, $path);
    }


    protected function load($namespace, $merge = [])
    {

        $settings = $this->repository->load($this->environment, $namespace);

        if (!empty($merge)) {
            $settings = $this->arrayMergeRecursiveDistinct($settings, $merge);
        }

        $this->addParameters(
            $this->arrayMergeRecursiveDistinct(
                $this->parameters,
                [$namespace => $settings]
            ),
            true);

        $this->loaded[$namespace] = $settings;

        return $settings;
    }


    public function getRepository()
    {
        return $this->repository;
    }

    public function setRepository(Loader $loader)
    {
        $this->repository = $loader;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function saveParams($environment = "")
    {

        return $this->repository->saveParams($this->parameters, $environment);

    }

}