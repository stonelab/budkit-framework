<?php

namespace Budkit\Parameter\Repository\Parser;

use Budkit\Filesystem\File;
use Budkit\Parameter\Repository\Handler;
use Budkit\Validation\Validate;


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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/config
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
final class Ini extends File implements Handler
{

    /**
     * Config file params
     *
     * @var type
     */
    public $namespace = array();


    protected $searchPath = DIRECTORY_SEPARATOR;


    public function __construct($searchPath = "")
    {

        $this->searchPath = $searchPath;

    }

    /**
     * Parses an INI configuration file
     *
     * @param type $filename
     * @return boolean
     */
    public function readParams($filename)
    {

        //We will only parse the file if it has not already been parsed!;
        if (!array_key_exists($filename, $this->namespace)) {
            if (file_exists($filename)) {
                if (($this->namespace[$filename] = parse_ini_file($filename, true)) === FALSE) {
                    throw new Exception("Could not Parse the ini file {$filename}");
                    return false;
                } else {
                    //Add the iniParams to $this->params;
                    return $this->namespace[$filename];
                }
            } else {
                // throw new Exception( sprintf("The configuration file (%s) does not exists",$filename ) );
                return [];
            }
        } else {
            return $this->namespace[$filename];
        }
    }

    /**
     * Save configuration param section or sections to an ini file
     *
     * @param type $file
     * @param type $sections
     */
    public function saveParams(array $parameters, $environment = "")
    {


        foreach ($parameters as $namespace => $section) {

            $_file = PATH_CONFIG . DS . strtolower($namespace) . ".ini";
            $_globals = "";

            foreach ($section as $key => $params) {

                if (!empty($params) && is_array($params)) {
                    // 2 loops to write `globals' on top, alternative: buffer
                    $_globals .= static::toIniString($params, $key);
                }
            }

            //Only save if the config file is not empty

            if (!empty($_globals)) {
                //check if we have a file;
                if (!$this->isFile($_file)) {
                    if (!$this->create($_file)) {
                        throw new \Exception("Could not create the configuration file {$_file}. Please check you have sufficient permission to do this");
                        return false;
                    }
                }

                //write to file
                if (!$this->write($_file, $_globals)) {
                    throw new \Exception(t("Could not write out to the configuration file"));
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns the read ini file parameters
     *
     * @param type $filename
     * @return type
     */
    public function getParams($filename = "")
    {

        if (empty($filename)) {
            return $this->namespace;
        } elseif (!empty($filename) && isset($this->namespace[$filename])) {
            return $this->namespace[$filename];
        } else {
            return array(); //if the params don't exists;
        }
    }

    /**
     * Converts a config array of elements to an ini string
     *
     * @param type $params
     * @param type $section
     * @return string
     */
    public static function toIniString($params = array(), $section = NULL)
    {

        $_br = "\n";
        $_tab = NULL; //Use "\t" to indent;
        $_globals = !empty($section) ? "\n[" . $section . "]\n" : '';

        foreach ($params as $param => $value) {
            if (!is_array($value)) {
                $value = static::normalizeValue($value);
                //BUG: Non alphanumeric value need to be stored in double quotes
                $_globals .= $_tab . $param . ' = ' . (Validate::isAlphaNumeric($value) ? $value : '"' . $value . '"') . $_br;
            }
        }
        return $_globals;
    }


    /**
     * normalize a Value by determining the Type
     *
     * @param string $value value
     *
     * @return string
     */
    protected static function normalizeValue($value)
    {
        if (is_bool($value)) {
            $value = (bool)$value;
            return $value;
        } elseif (is_numeric($value)) {
            return (int)$value;
        }
        return $value;
    }
}