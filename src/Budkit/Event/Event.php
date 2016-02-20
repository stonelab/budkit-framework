<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 04/07/2014
 * Time: 20:05
 */

namespace Budkit\Event;

class Event
{

    public $data = null;
    protected $attributes = [];
    protected $name = null; //the object that generated this event;
    protected $object = null; //passed to the listener
    protected $result = null; //obtained from listener
    protected $stopped = false; //has event execution finished?

    public function __construct($name, $object = null, $data = null)
    {
        $this->name = $name;
        $this->data = $data;

        $this->object = $object;
    }

    public function __get($attribute)
    {
        return $this->get($attribute);
    }

    /**
     * For storing event attributes;
     *
     * To replace, just set the first parameter as an array of attributes
     *
     * @param $attribute
     * @param string $value
     *
     */
    public function set($attribute, $value=""){

        if(is_array($attribute) && empty($value)) {
            $this->attributes = (array)$attribute;
            return true;
        }
        $this->attributes[$attribute] = $value;

    }

    public function get($name , $default = null )
    {
        //print_R($this->attributes);

        return (array_key_exists($name, $this->attributes))
            ? $this->attributes[$name]
            : (property_exists($this, $name) ?
                $this->$name : $default );
    }

    public function getData($key = null)
    {
        if (empty($key)) return $this->data;
        if (!empty($key) && !isset($this->data[$key])) return [];

        return $this->data[$key];
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult( $result)
    {
        $this->result = $result;
    }

    public function isStopped()
    {
        return $this->stopped;
    }

    public function stop()
    {
        $this->stopped = true;
    }
}