<?php

namespace Budkit\Helper;


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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/object
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
abstract class Helper
{

    /**
     * A bunch of hooks to run at various stages during parsing
     *
     * @var type
     */
    public static $hooks = array();
    /**
     * An error of object messages
     *
     * @var array
     */
    protected static $errors = array();

    /**
     * Sets an error
     *
     * @param type $error
     */
    final public static function setError($error)
    {
        array_push(self::$errors, $error);
    }

    /**
     * Creates an instance of the object
     *
     * @return void
     */
    public function Helper()
    {

        $args = func_get_args();

        call_user_func_array(array(&$this, '__construct'), $args);
    }

    /**
     * Get a protected object property
     *
     * @param string $property
     * @param mixed $default
     * @return mixed
     */
    public function get($property, $default = null)
    {

        //if its an array of properties;
        if (is_array($property)) {
            $values = array();

            foreach ($property as $key => $prop) {
                if (isset($this->$prop)) {
                    $values[$prop] = $this->$prop;
                }
            }
            return (!empty($values)) ? $values : $default;
        }

        if (isset($this->$property)) {
            return $this->$property;
        }
        return $default;
    }

    /**
     * Returns a referenced error
     *
     * @param type $i
     * @param type $toString
     * @return type
     */
    final public function getError($i = null, $toString = true)
    {

        // Find the error
        if ($i === null) {
            // Default, return the last message
            $error = end(self::$errors);
        } else
            if (!array_key_exists($i, self::$errors)) {
                // If $i has been specified but does not exist, return false
                return false;
            } else {
                $error = self::$errors[$i];
            }

        return $error;
    }

    /**
     * Returns the error string
     *
     * @return type
     */
    final public function getErrorString()
    {

        $errors = self::getErrors();
        $string = '<ul>';
        foreach ($errors as $error) {
            $string .= '<li>' . $error . '</li>';
        }

        //An ordered lists of all errors;
        return $string . '</ul>';
    }

    /**
     * Returns all the errors
     *
     * @return type
     */
    final public function getErrors()
    {
        return self::$errors;
    }

    /**
     * Sets an object property
     *
     * @param type $property
     * @param type $value
     * @return type
     */
    public function set($property, $value = null, $overwrite = false)
    {

        $previous = isset($this->$property) ? $this->$property : null;

        $this->$property = $value;

        return $previous;
    }

    /**
     * Set multiple object properties
     *
     * @param type $properties
     * @return type
     */
    final public function setProperties($properties)
    {
        $properties = (array)$properties; //cast to an array

        if (is_array($properties)) {
            foreach ($properties as $k => $v) {
                $this->$k = $v;
            }
            return true;
        }
        return false;
    }

    /**
     *
     * @return type
     */
    final public function toString()
    {
        return get_class($this);
    }

    /**
     * This method prevents object cloning
     *
     * @return void;
     */
    final public function __clone()
    {

    }

    /**
     * Solution to passing data by reference,
     *
     * http://ca.php.net/manual/en/mysqli-stmt.bind-param.php#96770
     * @param type $arr
     * @return type
     */
    final public function referencedArgs(&$arr)
    {
        if (strnatcmp(phpversion(), '5.3') >= 0) { //Reference is required for PHP 5.3+
            $refs = array();
            foreach ($arr as $key => $value)
                $refs[] = &$arr[$key];
            return $refs;
        }

        return $arr;
    }

}
