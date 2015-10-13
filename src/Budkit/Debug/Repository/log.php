<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * log.php
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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/log
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 */

namespace Library;

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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/log
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
class Log extends Object
{


    static $log;
    /**
     * A file resource where log message are stored
     * @var string
     */
    static $file = '';

    /**
     * Determines whether to enable the log or not
     * @var boolean
     */
    static $enable = FALSE;

    public function __construct()
    {
    }

    /**
     * Stores a message in the log file
     *
     * @param type $string
     * @param type $type
     */
    public static function message($string = '', $type = "message")
    {
    }

    /**
     * Stores an error message in the log file
     *
     * @param type $string
     * @param type $code
     */
    public static function error($string = '', $code = 404)
    {
    }

    /**
     * Returns the contents of the log file
     *
     * @return string
     */
    public static function getFile()
    {
    }

    /**
     * Returns the last message stored in the log
     *
     * @return array
     */
    public static function getLastMessage()
    {
    }

    /**
     * Logs a system message
     * @TODO relegate this function to the log class
     *
     * @param type $msgString
     * @param type $console
     * @param type $type
     * @param type $typekey
     * @param type $title
     * @param type $logFile
     * @return type
     */
    public static function _($string, $title = "Console Log", $type = "info", $typekey = "", $console = TRUE, $logFile = TRUE)
    {

        $output = \Library\Output::getInstance();
        //Remember to set the file;
        self::$file = Config::getParam("logfile", "system.log");

        //$output->addToPosition("do:console", $msgString , '', '', FALSE);

        //If isset this $log;
        $logkey = md5(strval($string . $console . $type . $logFile));

        if (!isset(self::$log[$logkey])) {
            self::$log[$logkey] = array(
                "title" => $title,
                "string" => $string,
                "type" => strtolower($type),
                "key" => $logkey,
                "time" => date("Y-m-d H:i:s"),
                "_toconsole" => $console,
                "_tofile" => $logFile
            );
        }

        //echo $msgString;

        //$output->appendToFile();
        //No html in log file
        return;
    }

    /**
     * Gets an instance of the logger class
     *
     * @staticvar self $instance
     * @return self
     */
    public static function getInstance()
    {

        static $instance;

        //If the class was already instantiated, just return it
        if (isset($instance))
            return $instance;

        $instance = new self;

        return $instance;
    }

    /**
     * Dumps the console log
     *
     * @return
     */
    public function dump()
    {
    }

    public function setLog()
    {
    }

    public function getLog()
    {
    }

    public function setMode()
    {
    }

    public function getConsole()
    {
    }
}