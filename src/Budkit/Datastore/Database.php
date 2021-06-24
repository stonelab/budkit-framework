<?php

namespace Budkit\Datastore;

use Budkit\Dependency\Container;


/**
 * Database abstraction handler
 *
 * This Database class is a database independent query interface definition.
 * It allows you to connect to different data sources like MySQL, SQLite and
 * other RDBMS on a Win32 operating system. Moreover the possibility exists to
 * use MS Excel spreadsheets, XML, text files and other not relational data
 * as data source.
 *
 * @category   Library
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 * @uses        Library\Database\ActiveRecord For magical Query building
 * @uses        Library\Database\Table For handling tablesets in the DB
 * @uses        Library\Database\Results For handling query resultsets
 * @uses        Library\Database\Drivers\MySQL\Driver For a MySQL abstraction;
 * @uses        Library\Database\Drivers\MySQLi\Driver For MySQLi;
 * @uses        Library\Database\Drivers\SQLite3\Driver For SQLite3;
 * @uses        Library\Database\Drivers\PostgreSQL\Driver For PostgreSQL;
 */
class Database
{

    //@TODO we need to find a better way to hide non static variables
    //This is set in in constructor but the intention is to hide it hidden;
    //protected $driver;

    //@TODO we need to find a better way to hide non static variables
    //This is set in in constructor but the intention is to hide it hidden;
    //private  $container;


    //@TODO we need to find a better way to hide non static variables
    //This is set in in constructor but the intention is to hide it hidden;
    //private $database;

    private $supported = [
        "mysqli" => Drivers\MySQLi\Driver::class,
        "postgresql" => Drivers\MySQLi\Driver::class,
        "sqlite" => Drivers\MySQLi\Driver::class,
        "mongodb" => Drivers\MySQLi\Driver::class,
    ];

    /**
     * Constructs the table object
     *
     * @param type $options
     */
    public function __construct($driver, $options = [])
    {

        $this->container = new Container();

        if (is_callable($this->supported[$driver])) {

            throw new \Exception("The requested database driver is not supported");
        }
        $this->database = $this->container->createInstance($this->supported[$driver], [$options]);

        $this->driver = $driver;

    }

    /**
     * For active record querying ONLY
     *
     * @param string $method
     * @param mixed $args
     * @return mixed
     */
    final public function __call($method, $args)
    {
        $engine = $this->database;

        if (!\is_callable([$engine, $method])) {
            throw new \Exception("The requested Database::{$method} is not not callable");
            return false;
        }

        return @\call_user_func_array([$engine, $method], $args);
    }

}