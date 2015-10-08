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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/date/time
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
class Time extends Date {

    /**
     * Get the current timestamp;
     * 
     * @return string
     */
    public static function now() {
        //returns the current timestamp
        return \time();
    }

    /**
     * Converts time to timestamp
     * 
     * @param type $time 
     * @return string
     */
    public static function stamp($time = null) {
        //converts a time to timestamp
        return date("Y-m-d H:i:s");
    }

    /**
     * Translates a string to a time
     * 
     * @return string
     */
    public static function translate($timestring) {
        //Translate a human string to time
    }
    
    /**
     * Get time difference between 2 times
     * 
     * @param string $time
     * @param string $now
     * @param array $options
     * @return string 
     */
    public static function difference($time, $now = NULL, $opt=array()) {
        //calculates the difference between two times
        //could be a string, or language
        //default now is NULL
        //Solve the 4 decades issue
        if (date('Y-m-d H:i:s', $time) == "0000-00-00 00:00:00" || empty($time)) {
            return _t('Never');
        }

        $defOptions = array(
            'to' => $now,
            'parts' => 1,
            'precision' => 'sec',
            'distance' => true,
            'separator' => ', '
        );
        $opt = array_merge($defOptions, $opt);
        
        //If now is empty then set now is to time now;
        if(!$opt['to']) $opt['to'] = time() ;
        
        
        $str = '';
        $diff = ($opt['to'] > $time) ? $opt['to'] - $time : $time - $opt['to'];
        $periods = array(
            'decade' => 315569260,
            'year' => 31556926,
            'month' => 2629744,
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'min' => 60,
            'sec' => 1);

        if ($opt['precision'] != 'sec') {
            $diff = round(($diff / $periods[$opt['precision']])) * $periods[$opt['precision']];
        }
        (0 == $diff) && ($str = 'less than 1 ' . $opt['precision']);
        foreach ($periods as $label => $value) {
            (($x = floor($diff / $value)) && $opt['parts']--) && $str .= ( $str ? $opt['separator'] :
                            '') . ($x . ' ' . $label . ($x > 1 ? 's' : ''));
            if ($opt['parts'] == 0 || $label == $opt['precision']) {
                break;
            }
            $diff -= $x * $value;
        }
        $opt['distance'] && $str .= ( $str && $opt['to'] > $time) ? ' ago' : ' ago'; //($str && $opt['to'] > $time) ? ' ago' : ' away';

        return $str;
    }

}