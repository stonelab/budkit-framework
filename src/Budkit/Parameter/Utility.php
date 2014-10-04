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

    protected $parameters = array();

    protected static $raw = array();

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

    public function addParameters(array $parameters = array(), $replace = false)
    {
        $this->parameters = $replace? array_replace($this->parameters, $parameters)
            : array_merge_recursive($this->parameters, $parameters);
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
                    throw new \InvalidArgumentException(sprintf('Malformed path. Unexpected "%s" at position %d.', $char, $i));
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