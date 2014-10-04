<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 04/07/2014
 * Time: 20:05
 */

namespace Budkit\Parameter;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use Budkit\Parameter\Utility;
use Budkit\Validation\Sanitize;
use Budkit\Validation\Validate;

class Factory implements ArrayAccess, IteratorAggregate, Countable
{

    use Utility;

    public static $validator; //stores the parameter name;
    public static $sanitizer; //The parameter validate methods;
    protected static $types = array(); //the parameter sanitize methods
    protected $group = null;

    public function __construct($group, array $parameters = array(), Sanitize $sanitizer = null, Validate $validator = null, $sanitize = true)
    {

        $this->group = $group;

        if ($sanitize) {

            static::$validator = $validator ? : new Validate();
            static::$sanitizer = $sanitizer ? : new Sanitize($parameters, FILTER_DEFAULT, array(), static::$validator);

            //we will sanitize everything! or at least try!
            //resanitize with specific methods for specific data types
            $this->parameters = static::$sanitizer->getData();
			
			

        } else {
            $this->parameters = $parameters;
        }
        //$this->setRawParameters($parameters);

    }

    /**
     * Get a comma seperated list of params as a Parameter object;
     *
     * e.g key1=value1;q=0.31,key2=value2....
     *
     * @param $name
     */
    public function getParameterListAsObject($name, $delimiter = ";")
    {

        $parameter = $this->getValidParameterOfType($name, "string");
        $list = preg_split('/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/', $parameter, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        //if emptyparameter return throw an exception because they'd be expecting an object;


        $parameters = array();
        $values = array();

        foreach ($list as $itemValue) {
            //if items have qualities associated with them we shall sort the parameter array by qualities;
            $bits = preg_split('/\s*(?:;*("[^"]+");*|;*(\'[^\']+\');*|;+)\s*/', $itemValue, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            $value = array_shift($bits);
            $attributes = array();

            $lastNullAttribute = null;
            foreach ($bits as $bit) {
                if (($start = substr($bit, 0, 1)) === ($end = substr($bit, -1)) && ($start === '"' || $start === '\'')) {
                    $attributes[$lastNullAttribute] = substr($bit, 1, -1);
                } elseif ('=' === $end) {
                    $lastNullAttribute = $bit = substr($bit, 0, -1);
                    $attributes[$bit] = null;
                } else {
                    $parts = explode('=', $bit);
                    $attributes[$parts[0]] = isset($parts[1]) && strlen($parts[1]) > 0 ? $parts[1] : '';
                }
            }

            $parameters[($start = substr($value, 0, 1)) === ($end = substr($value, -1)) && ($start === '"' || $start === '\'') ? substr($value, 1, -1) : $value] = $attributes;

        }

        return new Factory($name, $parameters, static::$sanitizer, static::$validator, false);
    }

    public function getValidParameterOfType($name, $type)
    {
        //sanitize to datatype;
        if (!method_exists(static::$sanitizer, strtolower($type))) {
            throw new \Exception("{$type} parameter type is not supported by the input sanitizer");
        }

        //get the data;
        return call_user_method($type, static::$sanitizer, $this->getParameter($name));
        //validate;
    }
} 