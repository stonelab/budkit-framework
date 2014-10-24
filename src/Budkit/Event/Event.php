<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 04/07/2014
 * Time: 20:05
 */

namespace Budkit\Event;

class Event {

public    $data    = null;
        protected $name    = null; //the object that generated this event;
    protected $object  = null; //passed to the listener
    protected $result  = null; //obtained from listener
    protected $stopped = false; //has event execution finished?

    public function __construct($name, $object = null, $data = null) {
        $this->name = $name;
        $this->data = $data;

        $this->object = $object;
    }

    public function __get($attribute) {
        return $this->get($attribute);
    }

    public function get($name) {
        return $this->{$name};
    }

    public function getData($key = null) {
        if (empty($key)) return $this->data;
        if (!empty($key) && !isset($this->data[ $key ])) return [];

        return $this->data[ $key ];
    }

    public function getResult() {
        return $this->result;
    }

    public function setResult($result) {
        $this->result = $result;
    }

    public function isStopped() {
        return $this->stopped;
    }

    public function stop() {
        $this->stopped = true;
    }
}