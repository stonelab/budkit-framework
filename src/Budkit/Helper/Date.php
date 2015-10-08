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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/date
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
class Date {
    /*
     * @var string
     */

    protected static $timestamp;
    
    
    public static function setDefaultTimeZone(){
        date_default_timezone_set('UTC');
    }

    /**
     * Returns todays date timestamp
     * 
     * @return string
     */
    public static function today(){
        
        return date('d/M/Y');
    }

    /**
     * Returns yesterdays date timestamp
     * 
     * @return string
     */
    public static function yesterday() {}

    /**
     * Translated from string to date
     * 
     * @param string $timestring
     * @return string A well formated date 
     */
    public static function translate($timestring) {
        //toggles between a valid timestamp and a string
        //attempts to create a timestamp from a string
        return strtotime($timestring);
    }

    /**
     * Returns the timestamp for the current date
     * 
     * @return string
     */
    public static function getTime() {
        //returns the timestamp for the current date
    }

}