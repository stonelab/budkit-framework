<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 03/07/2014
 * Time: 07:38
 */

namespace Budkit\Validation;


class Validate {

    /**
     * Uses a custom RegEx Pattern to validate a string. Or alternatively is_string()
     *
     * @param string $str The input string to be validated
     * @param string $regExp A custom input validation pattern
     * @param interger $length Optional string length specification
     * @return booleant true or false. Returns true if validation rules met, or false otherwise
     */
    public static function isString($str, $regExp=null, $length=null) {

        //Validate its a string;
        if(!is_string($str)){
            return FALSE;
        }
        //Patterns
        if(!empty($regExp)){
            $return = preg_match($regExp, $str);
            if(!(bool)$return){
                return FALSE;
            }
        }
        //Validate length;
        if(!empty($length) && static::isInterger( $length )){
            $length     = (int)$length; //@TODO if array check for min (index 0) and max (index 1) values;s
            $_length    = strlen( $str );
            //If the intergers don't match;
            if($length <> $_length){
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Validates a boolean datatype. Wrapper method for is_bool()
     *
     * @param mixed $bool the input data
     * @return boolean Returns true if datatype is boolean
     */
    public static function isBoolean($bool ) {
        return is_bool($bool);
    }

    /**
     * Validates a decimal
     *
     * @param string $dec
     * @return boolean True if is true decimal, False if not
     */
    public static function isDecimal( $decimal ) {

        $regEx  = '/^\s*[+\-]?(?:\d+(?:\.\d*)?|\.\d+)\s*$/';
        $return = preg_match($regex, $decimal);

        return ((int)$return > 0) ? TRUE : FALSE;
    }

    /**
     * Validates a character is alphanumeric
     *
     * @param string $alnum
     * @param interger $length
     * @return boolean True if is alphanumeric, false if not
     */
    public static function isAlphaNumeric($alnum, $length = null) {

        //Regular Expression
        $regEx = '/^[A-Za-z0-9_]+$/';

        //Validate the string;
        return static::string($alnum , $regEx , $length );
    }

    /**
     * Checks that a string is a timestamp
     *
     * @param string $tstamp
     * @return boolean True if is timestamp, false if not;
     */
    public static function isTimestamp($tstamp) {
        return ((string)(int)$tstamp === $tstamp)
        && ($tstamp <= PHP_INT_MAX)
        && ($tstamp >= ~PHP_INT_MAX);
    }

    /**
     * Validate if a string is a float.
     *
     * @param string $flt
     * @return boolean True if is float, False if not.
     */
    public static function isFloat($flt) {
        return is_int($flt);
    }

    /**
     * Checks input variable $num is a number
     *
     * @param mixed $num input data
     * @return boolean True if is number, False if not
     */
    public static function isNumber($num) {
        return is_int($num);
    }

    /**
     * Validates an interger. Checks input $int is an Interger datatype
     *
     * @param mixed $int
     * @return boolean True if is interger, False if its not.
     */
    public static function isInterger($int) {
        return is_int($int);
    }


    /**
     * Validates an ip address format
     *
     * @param string $address
     * @return boolean True if it is a valid IP address, False if Not.
     */
    public static function isIP( $address ) {

        //Split the IP address of the form  into parts
        $parts = explode('.', $address);
        //=4 parts
        if(sizeof($parts)!=4){
            return FALSE;
        }
        foreach($parts as $part):
            if(empty($part) || !static::number($part) || $part > 255 ){
                return FALSE;
            }
        endforeach;
        return TRUE;
    }

    /**
     * Quick and easy email validation
     *
     * @param string $email
     * @return boolean True if is valid email address, False if not
     */
    public static function isEmail($email) {

        $isValid = true;
        //$isInValid  = false;
        //Find the last occurence of the @, to split local from domain
        $atIndex = strrpos($email, "@");

        if (is_bool($atIndex) && !$atIndex) {
            return false;
        } else {
            $domain = substr($email, $atIndex + 1);
            $local = substr($email, 0, $atIndex);

            //Check the lengths of the domain and local parts
            //The maximum length of a local part is 64 characters (RFC 2821 4.5.3.1).
            $localLen = strlen($local);
            $domainLen = strlen($domain);

            //Validation
            if ($localLen < 1 || $localLen > 64) {
                static::setError(_t("The local part of the email is not of valid lengths"));
                return false;
            } else if ($domainLen < 1 || $domainLen > 255) {
                static::setError(_t("The email domain exceeded maximum length"));
                return false;
            } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
                static::setError(_t("invalid end dot ('.') position in local of email"));
                return false;
            } else if (preg_match('/\\.\\./', $local)) {
                static::setError(_t("Two consecutive dots ('.') in local of email"));
                return false;
            } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
                static::setError(_t("Invalid character in domain part"));
                return false;
            } else if (preg_match('/\\.\\./', $domain)) {
                static::setError(_t("Two consecutive dots ('.') in domain of email"));
                return false;
            } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {

                if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
                    static::setError(_t("Invalid character in local of email"));
                    return false;
                }
            }
            if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
                static::setError(_t("The domain of email not found in DNS"));
                return false;
            }
        }
        return $isValid;
    }

} 