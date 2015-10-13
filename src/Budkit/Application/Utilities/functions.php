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
 * The plural version of _t(). Some languages have more than one form for plural messages dependent on the count.
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
        $str .= $letters{$pos};
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
