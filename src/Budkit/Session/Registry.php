<?php

namespace Budkit\Session;

/**
 * What is the purpose of this class, in one sentence?
 *
 * How does this class achieve the desired purpose?
 *
 * @category   Library
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/session/registry
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
class Registry {

    /**
     * Looks the registry prevents editing
     * 
     * @var boolean
     */
    private $lock = FALSE;

    /**
     * A 'namespace'
     * 
     * @var string
     */
    protected $name;

    /**
     * Registry contents
     * 
     * @var mixed 
     */
    protected $data;

    /**
     * Constructs the registry
     * 
     * @param type $name 
     */
    final public function __construct($name) {
        $this->name = $name;
    }

    /**
     * Returns the name of the current registry
     * 
     * @return string
     */
    final public function getName() {
        //Returns the name of the current Registry object
        return $this->name;
    }

    /**
     * Adds a value to the registry
     * 
     * @param type $varname
     * @param type $value
     * @return Registry 
     */
    final public function set($varname, $value) {

        //If its lock we can't edit
        if ($this->lock)
            return false;

        //Continue
        $previous = isset($this->data[$varname]) ? $this->data[$varname] : null;
        $this->data[$varname] = $value;

        //what to do with previous?

        return $this;
    }
    
    /**
     * Returns all the data that is stored in the registry;
     * 
     * @return type
     */
    final public function getAllData(){
        return $this->data;
    }

    /**
     * Gets the value of an item in the registry
     * 
     * @param type $varname
     * @return type 
     */
    final public function get($varname) {
        $previous = isset($this->data[$varname]) ? $this->data[$varname] : null;

        return $previous;
    }

    /**
     * Checks if data in this namespace is locked
     * 
     * @return type 
     */
    final public function isLocked() {
        return $this->lock;
    }

    /**
     * Locks the registry
     * 
     * @return void
     */
    final public function lock() {
        if (!isset($this->name) || empty($this->name) || ($this->name === 'default')) {
            //@TODO throw exception, namespace cannot be locked;
            return false;
        }
        //echo $this->name." is ".$locked;
        $this->lock = TRUE;
    }

    /**
     * Unlocks the registry
     * 
     * @return void
     */
    final public function unlock() {

        //We cannot unlock the auth namespace!
        if (strtolower($this->name) <> "auth") {
            $this->lock = FALSE;
        }
    }

    final public function delete($varname) {

        //If its lock we can't edit
        if ($this->lock) return false;

        $previous = isset($this->data[$varname]) ? $this->data[$varname] : null;

        if (!is_null($previous)) {
            unset($this->data[$varname]);
        }
        return $this;
    }
}