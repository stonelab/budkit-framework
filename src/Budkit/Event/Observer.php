<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 04/07/2014
 * Time: 20:05
 */

namespace Budkit\Event;

class Observer {


    protected $listeners = [];



    public function __construct(){

    }


    public function attach($callback, $eventName = null, &$params = []) {

        //get the event definitions
        if ($callback instanceOf Listener) {

            $definition = (array)$callback->definition(); //typecast to array;

            //check the definition links to a callable,
            foreach ($definition as $event => $callable) {

                //if callable is string, check Listener has method 'callable' and attach to method;
                if (is_string($callable) && method_exists($callback, $callable)) {
                    return $this->attach([$callback, $callable], $event, $params);
                }

                //if callable is a string, and points to a callable class,
                if (is_string($callable) && is_callable($callable)) {
                    return $this->attach($callable, $event, $params);
                }

                //if callable is an array, loop;
                if (is_array($callable)) {
                    foreach ($callable as $callback) {
                        $this->attach($callback, $event, $params);
                    }

                    return true;
                }
            }
        }

        //Now check that callback is callable and that we have an eventName;
        if (is_callable($callback) && !empty($eventName)) {

			//Check that we have not already attached this event;
			if(isset($this->listeners[$eventName])){
				if(array_search($callback, $this->listeners[$eventName], true) !== false){
					return true; //we already have this callback
				}
			}
            //Priority must be numeric
            //if callback is already set for this eventype, skip it;
            $priority = 1;

            //default priority is 1; the higher the more important
            if (is_array($params)) {

                if (isset($params['priority']) && !is_numeric($params['priority'])) unset($params['priority']);

                $params   = array_merge(['priority' => $priority], $params);
                $priority = $params['priority'];

            }

            $this->listeners[ $eventName ][ $priority ][] = [
                'callable' => $callback,
                'params'   => &$params
            ];
        }
    }

    /**
     * Removes a listerner from the callback tree;
     *
     * @param Listener $callback
     * @param string   $eventName
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function detach(Listener $callback, $eventName = null) {

        //get the event defintions
    }


    /**
     * Triggers an event;
     *
     * @param string $eventName
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function trigger($event) {

        if (is_string($event)) {
            $event = new Event($event);
        }
        $listeners = $this->getListeners($event->name);

        if (empty($listeners)) {
            return $event;
        }

        //Priorities... the bigger the more urgent
        $urgency   = ksort($listeners, SORT_NUMERIC);
        $callbacks = array_reverse($listeners, true);

        //Loop through callbacks;
        foreach ($callbacks as $priority => $listener) {
            if ($event->isStopped()) {
                break;
            }

            foreach ($listener as $callback) {
                if ($event->isStopped()) {
                    break;
                }
                $this->propergate($event, $callback);
            }
        }

        return $event;
    }

    /**
     * Returns the callbacks attached to a specific event;
     *
     * @param string $eventName
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function getListeners($eventName = null) {

        if (empty($eventName)) return $this->listeners;
        if (!empty($eventName) && !isset($this->listeners[ $eventName ])) return [];

        return $this->listeners[ $eventName ];
    }

    /**
     * Propergate the event to all callbacks;
     *
     * @param string $event
     * @param string $callback
     *
     * @return void
     * @author Livingstone Fultang
     */
    protected function propergate(&$event, $callback) {

        //Pass the event, the data can be obtained from $event->data;
        $result = call_user_func($callback['callable'], $event, $callback['params']);

        //Result false can be indication of an error?
        if ($result === false) {
            $event->stop();
        }

        //Anything new to say?
        if ($result !== null) {
            $event->setResult($result);
        }
    }
}