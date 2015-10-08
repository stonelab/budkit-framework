<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * debugger.php
 *
 * Requires PHP version 5.3
 *
 * LICENSE: This source file is subject to version 3.01 of the GNU/GPL License
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.txt  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 * send a note to support@stonyhillshq.com so we can mail you a copy immediately.
 *
 * @category   Utiltities
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/graph
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 */

namespace Platform ;

use Library;

/**
 * What is the purpose of this class, in one sentence?
 *
 * How does this class achieve the desired purpose?
 *
 * @category   Utitlities
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/utilities/debugger
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
final class Debugger extends Library\Log{

    static $time;

    static $memory;

    public function getCallStackDump(){}


    public static function getInstance(){
        static $instance = NULL;
        //If the class was already instantiated, just return it
        if (isset($instance))
            return $instance;

        $instance = new self();

        return $instance;
    }

    /**
     * Records the debugger and system start time
     *
     * @return void
     */
    public static function start(){
        
        static::$time   = microtime( true );
        static::$memory = memory_get_usage( true );
	
        self::log(  static::$time , _t("Start execution time") , "info" );       
    }
    
    /**
     * Provides an alias for the log message method
     * 
     * @param mixed $string
     * @param string $title
     * @param string $type
     * @param string $typekey
     * @param boolean $console
     * @param boolean $logFile
     * @return static::_()
     */
    public static function log($string,  $title="Console Log", $type="info",  $typekey="" ,$console=TRUE, $logFile=TRUE){
        return static::_($string,  $title, $type,  $typekey ,$console, $logFile);
    }

    /**
     * Records the debugger stop and stystem stop time
     * Ideally the last method to be called before the output is sent to the server
     *
     * @return void
     */
    public static function stop(){

        //Get usage data
        $now    = microtime( true );
        $speed  = number_format(1000*($now-static::$time), 2);
        $_memory= memory_get_usage( );
        $units  = array('Bytes','KB','MB','GB','TB','PB');
        $memory = @round($_memory/pow(1024,($i=floor(log($_memory,1024)))),2).' '.$units[$i];
        $queries= '0';
        //Get Query usage
        //@TODO this smells, a method to detect that the database has been installed;
        $installed = (bool)\Library\Config::getParam("installed",FALSE,"database");
        
        if($installed):
            
            $database = \Library\Database::getInstance();
            $queries  = $database->getTotalQueryCount();

        endif;
        
        //Log usage
        self::log( $now , _t("Stop execution time") , "info"  );
        
        $output     = \Library\Output::getInstance();
        $showMessage= \Library\Config::getParam("mode", 1 , "environment");
        if ((int)$showMessage < 2 ) {
            $output = \Library\Output::getInstance();
            $output->set("debug", array("displaylog"=>true ) );
            $output->set("debug", array("queries"=>$queries ) );
            $output->set("debug", array("log"=>static::$log ) );
        }
        //Set the debugger output
        $output->set("debug", array("start"=>static::$time ) );
        $output->set("debug", array("stop"=>$now ) );
        $output->set("debug", array("speed"=>$speed ) );
        $output->set("debug", array("memory"=> $memory ) );

        //Library\Date::difference($now, $speed);
        //print_R(static::getInstance());

        
    }

    public function __construct(){}

    public function __desstruct(){}
}