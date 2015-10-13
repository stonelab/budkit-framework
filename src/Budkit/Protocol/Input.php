<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * input.php
 *
 * Requires PHP version 5.3
 *
 * LICENSE: This source file is subject to version 3.01 of the GNU/GPL License
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.txt  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 * send a note to support@stonyhillshq.com so we can mail you a copy immediately.
 *
 * @category   Library
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/input
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 */

namespace IS;

const INTERGER = 257;
const BOOLEAN = 258;
const STRING = FILTER_SANITIZE_STRING;
const STRIPPED = 513;
const ENCODED = 514;
const SPECIAL_CHARS = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
const RAW = 516;
const EMAIL = 517;
const URL = 518;
const NUMBER = 519;
const DECIMAL = 520;
const ESCAPED = 521;
const CUSTOM = 1024;
const FLOAT = 259;

namespace Budkit\Protocol;

use Budkit\Dependency\Container;

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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/input
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
final class Input
{

    /**
     *
     * @var array
     */
    protected $sanitized = array();

    /**
     * The Library\Validate Object
     *
     * @var object
     */
    protected $validate;

    /**
     * Constructor for the Input class
     *
     * @return void
     */
    public function __construct(Container $platform)
    {

        $this->request = $platform->request;
        $this->validate = $platform->validate;

        //autosanitize;
        $this->sanitize();
    }

    /**
     *  Sanitizes all verbs in the input
     *
     * @return void;
     */
    private function sanitize()
    {

        //
    }

    /**
     * Unserializes a string
     *
     * @param type $string
     * @return type
     */
    public static function unserialize($string)
    {
        return unserialize(gzuncompress(base64_decode($string)));
    }

    /**
     * Serializes an array or object
     *
     * @param string $data
     * @return string
     */
    public static function serialize($data)
    {
        return base64_encode(gzcompress(serialize($data)));
    }

    /**
     * Returns unsafe input! *as is*
     *
     * @param string $verb
     */
    public function getRaw($verb = 'get', $default = array())
    {
        //FILTER_UNSAFE_RAW
        return ($raw = $this->data($verb)) ? $raw : $default;
    }

    /**
     *
     * @param type $verb
     * @param type $filter
     * @param type $flags
     * @param type $options
     * @return type
     */
    public function data($verb = 'get', $default = [])
    {

        //filter_input_array();
        $data = [];

        if ($verb == "attribute") {
            $attributes = $this->request->getAttributes();
            $data = is_array($attributes->getAllParameters()) ? $attributes->getAllParameters() : [];
        } else {
            if (($input = $this->request->getMethodData($verb)) != false) {
                $data = $input;
            }
        }

        return $data;
    }

    /**
     * Gets the contents of a cookie by name
     *
     * @param type $name
     * @return type
     */
    public function getCookie($name)
    {

        $filter = $default = '';

        $cookie = $this->getVar($name, $filter, $default, "cookies");

        //@TODO cookie before return
        return empty($cookie) ? false : $cookie;
    }

    /**
     * Gets an input variable. Attempts to determine what its type is
     * and returns a sanitized type
     *
     * @param string $name
     * @param interger $filter
     * @param string $verb
     * @param interger $flags
     * @param array $options
     */
    public function getVar($name, $filter = '', $default = '', $verb = 'get', $options = array())
    {

        if (strtolower($verb) == 'request') {
            $verb = $this->getVerb();
        }
        //just form casting
        $verb = strtolower($verb);

        if (!($input = $this->data($verb))) {
            return $default;
        }

        //Undefined
        if (empty($name) || !isset($input) || !isset($input[$name])) {
            if (isset($default) && !empty($default)) {
                return $default;
            } else {
                return null; //nothing for that name;
            }
        }

        //Do we have a filter;
        if (!isset($filter) || !is_int($filter)) {
            //PHP warns against using gettype to get type,
            //but its much easier than running every is_* to determine type
            //so fo now we go with gettype;
            $type = gettype($input[$name]);

            switch ($type) {
                case "interger":
                    $filter = \IS\INTERGER;
                    break;
                case "float":
                case "double":
                    $filter = \IS\FLOAT;
                    $options = array(
                        "flags" => FILTER_FLAG_ALLOW_SCIENTIFIC | FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND,
                        "options" => $options
                    );
                    break;
                case "string":
                    $filter = \IS\STRING;
                    $options = array(
                        "flags" => !FILTER_FLAG_STRIP_LOW,
                        "options" => $options
                    );
                    break;
                case "object": //@TODO,
                case "resource":
                case "NULL":
                case "unknown type":
                default:
                    $filter = \IS\RAW;
                    $options = array(
                        "flags" => !FILTER_FLAG_STRIP_LOW,
                        "options" => $options
                    );
                    break;
            }
        }
        //uhhhnrrr...
        $variable = $input[$name];

        //Pre treat;
        if (get_magic_quotes_gpc() && ($input[$name] != $default) && ($verb != 'files')) {
            $variable = stripslashes($input[$name]); //??
        }

        return $this->filter($variable, $filter, $options);
    }

    /**
     * Returns the verb curresponding to the
     * current request method
     *
     * @return string
     */
    public function getVerb()
    {

        $verb = $this->request->getMethod();

        return (string)strtolower($verb);
    }

    /**
     * Filters an Input variable
     *
     * @param interger $type
     * @param mixed $variablename
     * @param interger $filter
     */
    private function filter($var, $filter, $options = null)
    {

        //gets a specific external variable and filter it
        //determine what variable name is being used here;
        $vname = null;

        //@TODO To trust or not to trust?
        return filter_var($var, $filter, $options);
    }

    /**
     * Checks and removes registered globals
     *
     * @return void
     */
    public function unRegisterGlobals()
    {
        if (ini_get('register_globals')) {
            $SUPER_GLOBALS = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($SUPER_GLOBALS as $UNSAFE) {
                foreach ($GLOBALS[$UNSAFE] as $key => $var) {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }

    /**
     * Magic Quotes, StripSlashes
     *
     * @param string $name
     * @param string $verb
     */
    public function getEscapedVar($name, $default = '', $verb = 'get', $options = array())
    {
        //FILTER_SANITIZE_MAGIC_QUOTES
        //FILTER_SANITIZE_SPECIAL_CHARS
        $filter = \IS\ESCAPED; //FILTER_SANITIZE_NUMBER_INT

        $escaped = $this->getVar($name, $filter, $default, $verb, $options);

        //@TODO validate is interger before return
        return $escaped;
    }

    /**
     * Gets the input method (verb)
     *
     * POST, GET
     *
     * @return string;
     */
    public function getMethod()
    {

        $verb = $this->getVerb();
        $method = strtoupper($verb);
        return $method;
    }

    /**
     * Determines if the input method is of type POST or GET
     *
     * @param string $verb
     * @return boolean
     */
    public function methodIs($verb)
    {

        $method = $this->getVerb();
        $return = ($method === strtolower($verb)) ? true : false;

        return $return;
    }

    /**
     * Returns the original refering URL
     *
     * @param type $internalize
     * @return type
     */
    public function getReferer($internalize = TRUE)
    {

        return $this->getString("HTTP_REFERER", null, "server");
    }

    /**
     * Strip tags, and encodes special characters.
     *
     * @param string $name
     * @param string $verb
     * @param boolean $allowhtml
     * @param array $tags
     */
    public function getString($name, $default = '', $verb = 'get', $allowhtml = false, $tags = array())
    {
        //FILTER_SANITIZE_STRING
        //FILTER_SANITIZE_STRIPPED
        //\IS\HTML;

        $filter = (!(bool)$allowhtml) ? \IS\STRING : \IS\SPECIAL_CHARS;
        $options = array(
            "flags" => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_ENCODE_HIGH,
            "options" => array()
        );

        //if (is_array($html)) $str = strip_tags($str, implode('', $html));
        //elseif (preg_match('|<([a-z]+)>|i', $html)) $str = strip_tags($str, $html);
        //elseif ($html !== true) $str = strip_tags($str);
        $string = $this->getVar($name, $filter, $default, $verb, $options);

        //Sub processing for HTML and all that?

        return (string)trim($string);
    }

    /**
     * Remove all characters except digits, plus and minus sign.
     *
     * @param type $name
     * @param type $verb
     */
    public function getInt($name, $default = '', $verb = 'get', $options = array())
    {

        $filter = \IS\INTERGER; //FILTER_SANITIZE_NUMBER_INT

        $interger = $this->getVar($name, $filter, $default, $verb, $options);

        //@TODO validate is interger before return
        return (int)$interger;
    }

    /**
     *
     * @param type $name
     * @param type $default
     * @param type $verb
     * @param type $options
     * @return type
     */
    public function getNumber($name, $default = '', $verb = 'get', $decimal = false, $options = array())
    {

        $filter = ($decimal) ? \IS\FLOAT : \IS\NUMBER; //FILTER_SANITIZE_NUMBER_INT

        $number = $this->getVar($name, $filter, $default, $verb, $options);

        //@TODO validate is interger or double before return

        return (empty($number)) ? (int)'0' : (int)$number;
    }

    /**
     * Returns a formatted string
     *
     * @param type $name
     * @param type $default
     * @param type $verb
     * @param type $allowedtags
     */
    public function getFormattedString($name, $default = '', $verb = 'post', $blacklisted = array())
    {
        //FILTER_SANITIZE_STRING
        //FILTER_SANITIZE_STRIPPED
        //\IS\HTML;
        //just form casting
        if (strtolower($verb) == 'request') {
            $verb = $this->getVerb();
        }
        $verb = strtolower($verb);

        if (!($input = $this->data($verb))) {
            return $default;
        }

        //Undefined
        if (empty($name) || !isset($input) || !isset($input[$name])) {
            if (isset($default) && !empty($default)) {
                return $default;
            } else {
                return null; //nothing for that name;
            }
        }
        //uhhhnrrr...
        $string = $input[$name];


        //print_R($string);

        //DOMDocument will screw up the encoding so we utf8 encode everything?
        $string = mb_convert_encoding($string, 'utf-8', mb_detect_encoding($string));
        $string = mb_convert_encoding($string, 'html-entities', 'utf-8');

        //if string is empty no need to proceed;

        if (empty($string)) return $string;


        $doc = new \DOMDocument('1.0', 'UTF-8');
        //$doc->substituteEntities = TRUE;
        $doc->loadHTML($string); //Load XML here, if you use loadHTML the string will be wrapped in HTML tags. Not good.
        $xpath = new \DOMXPath($doc);
        //@TODO remove tags that are not allowed;
        //Remove attributes
        $nodes = $xpath->query('//*[@style]');

        foreach ($nodes as $node):
            $node->removeAttribute('style'); //Removes the style attribute;
        endforeach;

        //We don't want to wrap in HTML tags
        $original = $xpath->query("body/*");
        if ($original->length > 0) {
            $string = '';
            foreach ($original as $node) {
                //cannot use doc->saveElement because it wraps it in p tags!
                //so lets just go ahead and use the nodevalue;
                $string .= $node->nodeValue;
            }
        }

        $filter = \IS\SPECIAL_CHARS;
        $options = array(
            "flags" => FILTER_FLAG_ENCODE_LOW, //or strip?
            "options" => array()
        );

        //if (is_array($html)) $str = strip_tags($str, implode('', $html));
        //elseif (preg_match('|<([a-z]+)>|i', $html)) $str = strip_tags($str, $html);
        //elseif ($html !== true) $str = strip_tags($str);
        //Some tags we really don't need
        $string = $this->filter($string, $filter, $options);

        return $string;
    }

    /**
     * Returns a cleaned Array
     *
     * @param string $name
     * @param string $verb
     * @param array $flags
     */
    public function getArray($name, $default = [], $verb = 'get', $options = array())
    {

        if (strtolower($verb) == 'request') {
            $verb = $this->getVerb();
        }

        //just form casting
        $verb = strtolower($verb);

        if (!($input = $this->data($verb))) {
            return $default;
        }

        //Undefined
        if (empty($name) || !isset($input) || !isset($input[$name])) {
            if (isset($default) && !empty($default)) {
                return $default;
            } else {
                return null; //nothing for that name;
            }
        }

        //FILTER_SANITIZE_STRING
        //FILTER_SANITIZE_STRIPPED
        //\IS\HTML;
        $filter = \IS\CUSTOM;  //FILTER_CALLBACK;
        $options = array(
            "flags" => FILTER_REQUIRE_ARRAY,
        );

        //uhhhnrrr...
        $array = $input[$name];

        //Use the call back filter to clean this array
        //Sub processing for HTML and all that?
        return (array)$array;
    }

    public function getFloat($name, $default = '', $verb = 'get', $options = array())
    {

        //FILTER_SANITIZE_NUMBER_FLOAT
        //FILTER_SANITIZE_NUMBER_FLOAT
        $filter = \IS\FLOAT;
        $options = array(
            "flags" => FILTER_FLAG_ALLOW_SCIENTIFIC | FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND,
            "options" => $options
        );

        $float = $this->getVar($name, $filter, $default, $verb, $options);

        //@TODO valid is float before return

        return (float)$float;
    }

    /**
     * Remove all characters except digits 0 and 1.
     * Transforms into Boolean true or false, where 0=false and 1=true
     *
     * @param string $name
     * @param string $verb
     */
    public function getBoolean($name, $default = '', $verb = 'get', $options = array())
    {
        //FILTER_SANITIZE_NUMBER_INT
        $filter = \IS\BOOLEAN;
        $options = array(
            "options" => $options
        );

        $boolean = $this->getVar($name, $filter, $default, $verb, $options);

        //@TODO valid is float before return

        return (boolean)$boolean;
    }

    /**
     * Returns the first word a santized string
     * Strip tags, optionally strip or encode special characters.
     *
     * @param string $name
     * @param string $verb
     * @param array $flags
     */
    public function getWord($name, $default = '', $verb = 'get', $options = array())
    {
        //First word in a sanitized string
        $sentence = $this->getString($name, $default, false);

        //@TODO validate string before breaking into words;
        //Requires php5.3!!
        return (string)strstr($sentence, ' ', true);
    }

    /**
     * Remove all characters except letters, digits and !#$%&'*+-/=?^_`{|}~@.[].
     *
     * @param string $name
     * @param string $verb
     */
    public function getEmail($name, $default = '', $verb = 'get', $options = array())
    {
        //FILTER_SANITIZE_EMAIL

        $filter = \IS\EMAIL;
        $options = array(
            "options" => $options
        );
        $email = $this->getVar($name, $filter, $default, $verb, $options);

        //@TODO valid is email with $this->validate before return;
        return (string)$email;
    }

    /**
     * Filters an Array recursively
     *
     * @param type $array
     */
    private function filterArray($array)
    {
        //
    }

}