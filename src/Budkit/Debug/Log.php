<?php

//@TODO catch E_NOTICE and E_WARNING exceptions and log to console
//@TODO create a visible console if environment mode is debug

namespace Budkit\Debug;

use Budkit\Filesystem\File;
use Exception;

/**
 *
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
 *
 */
class Log{


    var $log = [];
    /**
     * A file resource where log message are stored
     * @var string
     */
    var $file = '';

    /**
     * Determines whether to enable the log or not
     * @var boolean
     */
    var $enable = FALSE;


    protected $application;


    public  function __construct($file = "console.log"){
        $this->file = $file;
    }

    public  function message($string=''){
        return $this->_($string, "message");
    }

    public function object($object, $message, $code){
        $this->_($message, "object", $code, $object);
    }


    public function error($string='',$code=404){}

    public function getFile(){}

    public function getLastMessage(){}

    public function dump(){}

    public function setLog(){}

    public function getLog(){}

    public function setMode(){}

    public function getConsole(){}

    protected function _( $string,  $type="info",  $code = 200, $object = []){
        	
        //If isset this $log;
        $logkey = md5(strval( $string.$type.$code) );

        if(!isset($this->log[$this->file])) {
            $this->log[$this->file] = [];
        }

        array_push(
            $this->log[$this->file] ,  array(
                "string"    => $string ,
                "type"      => strtolower( $type ),
                "code"      => strtolower( $code ),
                "key"       => $logkey,
                "object"    => $object,
                "time"      => date("Y-m-d H:i:s")
            )
        );

        return;
    }

    /**
     * Ticks a user performed action
     *
     * @param $class
     * @param array $params
     * @param null $usernameid
     * @param bool|false $decrement
     * @return bool
     * @throws Exception
     */
    public function tick($class, array $params = array(), $usernameid = NULL, $decrement = false) {

        if (empty($class)):
            return false; //We need to know the class to tick
        endif;

        $handler    = new File();
        $file       = date("Y-m-d") . ".log";
        $folder     = PATH_LOGS.DS.$class.DS;

        if (!$handler->isFile($folder.$file)) { //if its not a folder
            if (!$handler->create($folder.$file, "a+")) {
                throw new Exception("Could not create the log file {$file}");
                return false;
            }
        }

        unset($params["file"]);

        $tick = array_merge(array("time"=>time(), "inc"=>(!$decrement)?"+1":"-1"), $params);
        $line = json_encode($tick);

        if (!$handler->write($folder.$file, PHP_EOL.$line, "a+")) {
            throw new Exception("Could not write out to the stats file {$file}");
            return false;
        }

        return true;
    }
}