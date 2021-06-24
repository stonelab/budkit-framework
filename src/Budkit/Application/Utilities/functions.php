<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * functions.php
 *
 * Requires PHP version 5.3
 *
 * LICENSE: This source file is subject to version 3.01 of the GNU/GPL License
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.txt  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 * send a note to support@stonyhillshq.com so we can mail you a copy immediately.
 *
 * @category   Utility
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/utilities/framework
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 */

/**
 * Lookup a message in the current domain
 *
 * @param string $message
 */
function t($message)
{

//    $Framework  = \Platform\Framework::getInstance();
//    $i18n       = $Framework->get('i18n');
//
//     //print_R($Framework);


    //die;

    return $message;
}

/**
 * The plural version of t().
 *
 * Some languages have more than one form for plural messages dependent on the count.
 *
 * @param string $msgid1
 * @param string $msgid2
 * @param int $n
 *
 */
function tn($msgid1, $msgid2, $n)
{
}

/**
 * This function allows you to override the current domain for a single message lookup
 *
 * @param string $domain
 * @param string $message
 */
function td($domain, $message)
{
}

/**
 * This function allows you to override the current domain for a single plural message lookup.
 *
 * @param string $domain
 * @param string $msgid1
 * @param string $msgid2
 * @param int $n
 */
function tdn($domain, $msgid1, $msgid2, $n)
{
}

/**
 * This function allows you to override the current domain for a single message lookup in any category
 *
 * @param string $domain
 * @param string $message
 * @param int $category
 */
function tdc($domain, $message, $category)
{
}

/**
 * This function allows you to override the current domain for a single plural message lookup in any category
 *
 * @param string $domain
 * @param string $msgid1
 * @param string $msgid2
 * @param int $n
 * @param int $category
 */
function tdcn($domain, $msgid1, $msgid2, $n, $category)
{
}


/**
 * Generates a random hex collor
 *
 * @return type
 */
function getRandomColor()
{
    $letters = "1234567890ABCDEF";
    $str = "";
    while (strlen($str) < 6) {
        $pos = rand(1, 16);
        $str .= $letters[$pos];
    }
    return $str;
}


function getRandomString($length = 10, $lowercase = false, $startWithInt = false)
{

    $length = intval($length);
    $starters = "0123456789";
    $limit = ($startWithInt) ? $length - 1 : $length;

    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    if ($lowercase):
        $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
    endif;
    $randomString = '';

    //Start with a number?
    if ($startWithInt) {
        $randomString .= $starters[rand(0, 9)];
    }

    for ($i = 0; $i < $limit; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}


/**
 * Takes an ArrayObject and turn it into an associative array
 *
 * @param ArrayObject $obj
 *
 * @return array
 */
function getArrayObjectAsArray( $obj )
{
    $array  = array(); // noisy $array does not exist
    $arrObj = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($arrObj as $key => $val) {
        $val = (is_array($val) || is_object($val)) ? getArrayObjectAsArray( $val ) : $val;
        $array[$key] = $val;
    }
    return $array;
}


/**
 * Inserts an item into an array at a specific position
 *
 * Source: http://stackoverflow.com/questions/3353745/how-to-insert-element-into-arrays-at-specific-position
 *
 * @param $array
 * @param $search_key
 * @param $insert_key
 * @param $insert_value
 * @param bool $insert_after_founded_key
 * @param bool $append_if_not_found
 * @return array
 */
function insertIntoArray( $array, $search_key, $insert_key, $insert_value, $insert_after_founded_key = true, $append_if_not_found = false ) {

    $new_array = array();

    foreach( $array as $key => $value ){

        // INSERT BEFORE THE CURRENT KEY?
        // ONLY IF CURRENT KEY IS THE KEY WE ARE SEARCHING FOR, AND WE WANT TO INSERT BEFORE THAT FOUNDED KEY
        if( $key === $search_key && ! $insert_after_founded_key )
            $new_array[ $insert_key ] = $insert_value;

        // COPY THE CURRENT KEY/VALUE FROM OLD ARRAY TO A NEW ARRAY
        $new_array[ $key ] = $value;

        // INSERT AFTER THE CURRENT KEY?
        // ONLY IF CURRENT KEY IS THE KEY WE ARE SEARCHING FOR, AND WE WANT TO INSERT AFTER THAT FOUNDED KEY
        if( $key === $search_key && $insert_after_founded_key )
            $new_array[ $insert_key ] = $insert_value;

    }

    // APPEND IF KEY ISNT FOUNDED
    if( $append_if_not_found && count( $array ) == count( $new_array ) )
        $new_array[ $insert_key ] = $insert_value;

    return $new_array;

}