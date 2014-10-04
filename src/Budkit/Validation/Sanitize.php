<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 03/07/2014
 * Time: 07:45
 */

namespace Budkit\Validation;

//use Budkit\Validation\Validate;

class Sanitize
{

    const FILTER_INTERGER = 257;
    const FILTER_BOOLEAN = 258;
    const FILTER_STRING = FILTER_SANITIZE_STRING;
    const FILTER_STRIPPED = 513;
    const FILTER_ENCODED = 514;
    const FILTER_SPECIAL_CHARS = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
    const FILTER_RAW = 516;
    const FILTER_EMAIL = 517;
    const FILTER_URL = 518;
    const FILTER_NUMBER = 519;
    const FILTER_DECIMAL = 520;
    const FILTER_ESCAPED = 521;
    const FILTER_CUSTOM = 1024;
    const FILTER_FLOAT = 259;


    /**
     *
     * @var array
     */
    protected $data = array();


    /**
     *
     * @var array
     */
    protected $sanitized = false;

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
    public function __construct($data = array(), $filter = FILTER_DEFAULT, array $options = array(), Validate $validate)
    {

        $this->validate = $validate; //the validator class;
        $this->data = $data;

        //FILTER_DEFAULT = 516;

        //if data is provided auto sanitize
        $this->data = empty($this->data) ? array() : $this->scrub($this->data, $filter, $options);

    }

    /**
     * @param array $default
     * @return array|mixed
     */
    public function getData($default = array())
    {					
        //to avoid ting unsanitized data from this method,
        return (!$this->sanitized) ? $default : $this->data;
    }


    /**
     * @param array $data
     * @param int $filter
     * @param array $options
     * @return mixed
     */
    public function data($data = array(), $filter = FILTER_DEFAULT, $options = array())
    {
        $sanitized = new static($data, $filter, $options, new Validate);
        return $sanitized->getData();
    }


    /**
     * @param $data
     * @param string $default
     * @param array $options
     * @return mixed
     */
    public function escaped($data, $default = "", $options = array())
    {
        //FILTER_SANITIZE_MAGIC_QUOTES
        //FILTER_SANITIZE_SPECIAL_CHARS
        $filter = \IS\ESCAPED;
        //FILTER_SANITIZE_NUMBER_INT

        $escaped = $this->getVar($name, $filter, $default, $verb, $options);

        //@TODO validate is interger before return
        return $escaped;
    }

    /**
     * @param $data
     * @param int $default
     * @param array $options
     * @return int
     */
    public function int($data, $default = 0, $options = array())
    {

        //check custom filters and flags in options array;
        list($filter, $flags, $parameters) = $this->readFiltersAndFlags(
            $options,
            static::FILTER_INTERGER
        );
        //push custom filters and flags from options to their respective array;

        $interger = $this->scrub($data, $filter, $parameters);

        //@TODO validate is interger before return
        return (int)$interger;
    }

    /**
     * @param $data
     * @param string $default
     * @param array $options
     * @param bool $float
     * @return array|mixed|string
     */
    public function number($data, $default = '0', array $options = array(), $float = false)
    {

        list($filter, $flags, $parameters) = $this->readFiltersAndFlags(
            $options,
            ($float) ? static::FILTER_FLOAT : static::FILTER_NUMBER
        );

        //push custom filters and flags from options to their respective array;
        $number = $this->scrub($data, $filter, $parameters);

        //@TODO validate is number before return
        return (empty($number)) ? $default : $number;

    }

    /**
     * @param $data
     * @param array $blacklisted
     * @return mixed|null|string
     */
    public function markup($data, $blacklisted = array())
    {
        //FILTER_SANITIZE_STRING
        //FILTER_SANITIZE_STRIPPED
        //\IS\HTML;
        //just form casting
        if (strtolower($verb) == 'request') {
            $verb = $this->getVerb();
        }
        $verb = strtolower($verb);
        $input = $this->$verb;
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

        //DOMDocument will screw up the encoding so we utf8 encode everything?
        $string = mb_convert_encoding($string, 'utf-8', mb_detect_encoding($string));
        $string = mb_convert_encoding($string, 'html-entities', 'utf-8');

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
                $string .= $doc->saveHTML($node);
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
     * Strip tags, and encodes special characters.
     *
     * @param string $name
     * @param string $verb
     * @param boolean $allowhtml
     * @param array $tags
     */
    public function string($data, $default = "", $options = array(), $allowmarkup = false, $blacklisted = array())
    {
        //FILTER_SANITIZE_STRING
        //FILTER_SANITIZE_STRIPPED
        //\IS\HTML;
        //check custom filters and flags in options array;
        list($filter, $flags, $parameters) = $this->readFiltersAndFlags(
            $options,
            (!$allowmarkup) ? static::FILTER_STRING : static::FILTER_SPECIAL_CHARS,
            array(FILTER_FLAG_STRIP_LOW, FILTER_FLAG_ENCODE_HIGH)
        );
        //push custom filters and flags from options to their respective array;

        $string = $this->scrub($data, $filter, $parameters);

        //@TODO validate is interger before return
        return (empty($string)) ? (string)$default : trim($string);
    }

    /**
     * @param $data
     * @param string $default
     * @param array $options
     * @param bool $allowmarkup
     * @param array $blacklisted
     * @return float
     */
    public function float($data, $default = "", $options = array(), $allowmarkup = false, $blacklisted = array())
    {

        $options = array_merge_recursive($options, array("flags" => array(FILTER_FLAG_ALLOW_SCIENTIFIC, FILTER_FLAG_ALLOW_FRACTION, FILTER_FLAG_ALLOW_THOUSAND)));

        //@TODO validate is interger before return
        return (float)$this->number($data, $default, $options, true);

    }

    /**
     * @param $data
     * @param bool $default
     * @param array $options
     * @return bool
     */
    public function boolean($data, $default = false, $options = array())
    {

        //check custom filters and flags in options array;
        list($filter, $flags, $parameters) = $this->readFiltersAndFlags(
            $options,
            static::FILTER_BOOLEAN
        );
        //push custom filters and flags from options to their respective array;

        $boolean = $this->scrub($data, $filter, $parameters);

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
    public function word($data, $default = '', $options = array())
    {

        //First word in a sanitized string
        $sentence = $this->string($data, $default, $options, false);

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
    public function email($data, $default = 'missing@example.com', $options = array())
    {
        //FILTER_SANITIZE_EMAIL

        //check custom filters and flags in options array;
        list($filter, $flags, $parameters) = $this->readFiltersAndFlags(
            $options,
            static::FILTER_EMAIL
        );
        //push custom filters and flags from options to their respective array;

        $email = $this->scrub($data, $filter, $parameters);

        //@TODO valid is email with $this->validate before return;
        return (string)(empty($email) ? $default : $email);
    }


    /**
     * @param $variable
     * @param $filter
     * @param null $options
     * @return mixed
     */
    private function filter($variable, $filter, $options = null)
    {

        //gets a specific external variable and filter it
        //determine what variable name is being used here;
        $this->sanitized = true;

        //@TODO To trust or not to trust?
        return filter_var($variable, $filter, $options);
    }

    /**
     * Filters an Array recursively
     *
     * @param type $array
     */
    private function filterArray(array $data, array $options)
    {
        $this->sanitized = true;

        return filter_var_array($data, $options, true);
    }

    /**
     * @param array $options
     * @param $filter
     * @param array $flags
     * @return array
     */
    private function readFiltersAndFlags(array $options, $filter = DEFAULT_FILTER, $flags = array())
    {

        //Merge the flags
        $_flags = (isset($options['flags']) && is_array($options['flags']))
            ? array_merge($options['flags'], array())
            : $flags;

        unset($options['filters']);
        // unset($options['flags']);


        //The PHP default santize (filter) uses a defined option definition format
        // array("options"=>array(), "filters"=>array(), "flags"=>array())
        $parameters = array_merge_recursive($options, array("flags" => $_flags));

        return array($filter, $_flags, $parameters);

    }

    /**
     * @param $data
     * @param $filter
     * @param array $options
     * @return array|mixed
     */
    private function scrub($data, $filter = DEFAULT_FILTER, array $options = array())
    {
        //To filter a large array;
        if (is_array($data)) {
            $definedOptions = array_intersect_key($data, $options);
            if (count($data) == count($definedOptions)) {
                return $this->filterArray($data, $options);
            } else {
                $sanitized = array();
                foreach ($data as $key => $variable) {
                    $type = gettype($variable);

                    switch ($type) {
                        case "integer":
                            $sanitized[$key] = $this->int($variable);
                            break;
                        case "float":
                        case "double":
                            $sanitized[$key] = $this->float($variable);
                            break;
                        case "string":
                            $sanitized[$key] = $this->string($variable);
                            break;
                        case "object": //@TODO maybe serialize?,
                        case "resource":
							//there is not much to be done with objects;
							//maybe should come up with a way to check how safe they are;
							$this->sanitized = true;
							$sanitized[$key] = $variable; 
							break;
                        case "NULL":
                        case "unknown type":
                        default:
                            $sanitized[$key] = $this->filter($variable, static::FILTER_RAW, $options);
                            break;
                    }
                }
                return $sanitized;
            }
        }

        //bitwise disjunction of flags
        $options['flags'] = implode("|", $options['flags']);

        return $this->filter($data, $filter, $options);
    }
} 