<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 04/07/2014
 * Time: 19:45
 */

namespace Budkit\Parameter;

use ArrayIterator;

trait Utility
{

    protected static $raw = [];
    protected $parameters = [];

    public function getParameterKeys()
    {
        return array_keys($this->parameters);
    }

    public function hasParameter($name)
    {
        return $this->offsetExists($name);
    }

    public function offsetExists($name)
    {
        return isset($this->parameters[$name]);
    }

    public function setParameter($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function offsetSet($name, $value)
    {
        //add the route to the collection container;
        if (is_null($name)) {
            $this->parameters[] = $value;
        } else {
            $this->parameters[$name] = $value;
        }
    }

    public function addParameters(array $parameters = [], $replace = false)
    {
        $this->parameters = $replace ? array_replace($this->parameters, $parameters)
            : $this->arrayMergeRecursiveDistinct($this->parameters, $parameters);
    }

    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * arrayMergeRecursiveDistinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * arrayMergeRecursiveDistinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * If key is integer, it will be merged like array_merge do:
     * arrayMergeRecursiveDistinct(array(0 => 'org value'), array(0 => 'new value'));
     *     => array(0 => 'org value', 1 => 'new value');
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     * @author Anton Medvedev <anton (at) elfet (dot) ru>
     */
    protected function arrayMergeRecursiveDistinct(array $array1, array $array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset ($merged[$key]) && is_array($merged[$key])) {
                if (is_int($key)) {
                    $merged[] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
                } else {
                    $merged[$key] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
                }
            } else {
                if (is_int($key)) {
                    $merged[] = $value;
                } else {
                    $merged[$key] = $value;
                }
            }
        }
        return $merged;
    }

    public function removeParameter($name)
    {
        unset($this->parameters[$name]);
    }

    public function getParameter($path, $default = null, $deep = false)
    {
        if (!$deep || false === $pos = strpos($path, '[')) {
            return array_key_exists($path, $this->parameters) ? $this->parameters[$path] : $default;
        }

        $root = substr($path, 0, $pos);
        if (!array_key_exists($root, $this->parameters)) {
            return $default;
        }

        $value = $this->parameters[$root];
        $currentKey = null;
        for ($i = $pos, $c = strlen($path); $i < $c; $i++) {
            $char = $path[$i];

            if ('[' === $char) {
                if (null !== $currentKey) {
                    throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "[" at position %d.', $i));
                }

                $currentKey = '';
            } elseif (']' === $char) {
                if (null === $currentKey) {
                    throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "]" at position %d.', $i));
                }

                if (!is_array($value) || !array_key_exists($currentKey, $value)) {
                    return $default;
                }

                $value = $value[$currentKey];
                $currentKey = null;
            } else {
                if (null === $currentKey) {
                    throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "%s" at position %d.',
                        $char, $i));
                }

                $currentKey .= $char;
            }
        }

        if (null !== $currentKey) {
            throw new \InvalidArgumentException(sprintf('Malformed path. Path must end with "]".'));
        }

        return $value;
    }

    public function offsetGet($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    public function getAllParameters()
    {
        return $this->parameters;
    }

    public function setRawParameters(array $parameters)
    {
        static::$raw = $parameters;
    }

    public function getRawParameters()
    {
        return static::$raw;
    }

    public function offsetUnset($name)
    {
        unset($this->parameters[$name]);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->parameters);
    }

    public function count()
    {
        return count($this->parameters);
    }
} 