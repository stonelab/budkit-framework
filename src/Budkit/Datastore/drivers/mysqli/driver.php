<?php

namespace Budkit\Datastore\Drivers\MySQLi;

use Budkit\Datastore\Engine;

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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/database/drivers/mysql/driver
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
final class Driver implements \Budkit\Datastore\Driver{

    /**
     *  The Datastore engine, which implements
     *  additional methods
     *
     */
    use Engine;

    /**
     * The database driver name
     *
     * @var string
     */
    var $name = 'mysqli';
    /**
     *  The null/zero date string
     *
     * @var string
     */
    var $nullDate = '0000-00-00 00:00:00';
    /**
     * Quote for named objects
     *
     * @var string
     */
    var $nameQuote = '`';
    
    /**
     * Transaction queries
     * 
     * @var type 
     */
    var $transactions = array();


    /**
     * A container to manage datastore objects
     * @var
     */
    var $container;

    /**
     * Connects to the databse using the default DBMS
     *
     * @param string $name database name
     * @param string $server default is localhost
     * @param string $username if not provided default is used
     * @param string $password not stored in the class
     * @return bool TRUE on success and FALSE on failure
     */
    public function __construct($options = []){

        $host       = array_key_exists('host', $options) ? $options['host'] : 'localhost';
        $user       = array_key_exists('user', $options) ? $options['user'] : '';
        $password   = array_key_exists('password', $options) ? $options['password'] : '';
        $database   = array_key_exists('name', $options) ? $options['name'] : '';
        $prefix     = array_key_exists('prefix', $options) ? $options['prefix'] : 'dd_';
        $select     = array_key_exists('select', $options) ? $options['select'] : true;
        
        if(!$this->connect($host, $user, $password, $database, $prefix, $select )){

            //@TODO throw connection exceptions
            //throw new Exception("Could not connect to database with driver:mysqli");

            return false;
            //throw an exception;
        }

        // Determine utf-8 support
        $this->utf = $this->hasUTF();

        //Set charactersets (needed for MySQL 4.1.2+)
        if ($this->utf) {
            $this->setUTF();
        }
        
        $this->prefix = $prefix;
        $this->ticker = 0;
        $this->errorNum = 0;
        $this->log = array();
        $this->quoted = array();
        $this->hasQuoted = false;
        $this->debug    = true;

        // select the database
        if ($select) {
            $this->database($database);
        }
    }
    
    /**
     * Connects to the databse using the default DBMS
     *
     * @param string $name database name
     * @param string $server default is localhost
     * @param string $username if not provided default is used
     * @param string $password not stored in the class
     * @return bool TRUE on success and FALSE on failure
     */
    public function connect($server = 'localhost', $username = '', $password = '', $database = '' , $prefix='dd_' , $select = true) {
        
        if($this->isConnected()){
            return true;
        }
        
        // mysql driver exists?
        if (!function_exists('mysqli_real_connect')) {
            $this->errorNum = 1;
            $this->errorMsg = _t('The MySQLi extension "mysqli" is not available.');
            $this->setError( "[{$this->name}:{$this->errorNum}] {$this->errorMsg}");
            return false;
        }
        
        $this->resourceId = mysqli_init();
        
        if (!$this->resourceId) {
            $this->setError(_t('mysqli_init failed'));
        }
        
        //We want to keep autocomit on all the time exceplt when we are performing a transaction
        if (!$this->resourceId->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 1')) {
            $this->setError(_t('Setting MYSQLI_INIT_COMMAND failed'));
        }

        if (!$this->resourceId->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
            $this->setError(_t('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed'));
        }
        
        // connect to the server
        if (!$this->resourceId->real_connect($server, $username, $password, $database)) {
            $this->errorNum =  mysqli_connect_errno();
            $this->errorMsg =  mysqli_connect_error();
            
            $this->setError( "[{$this->name}:{$this->errorNum}] {$this->errorMsg}");
            return false;
        }
        
        // select the database
        if ($select) {
            if(!$this->database($database)){
                $this->close();
                return false;
            }
        }
        
        $this->prefix = $prefix;
        
        return true;
        
    }

   /**
    * Chooses the database to connect to
    * @param string $database
    * @return boolean
    */
   protected function database( $database ) {
        //Make sure its not empty
        if (!$database) {
            return false;
        }
        
        //Chooses the database to connect to
        if (!mysqli_select_db($this->resourceId, $database)) {
            $this->errorNum = 3;
            $this->errorMsg = _t('Could not connect to database');
            $this->setError( "[{$this->name}:{$this->errorNum}] {$this->errorMsg}");
            return false;
        }

        return true;
    }

    /**
     * Reports on the status of the established Object
     *
     * @return boolean
     */
    public function isConnected(){

        if (is_a($this->resourceId,"mysqli")) {
            return mysqli_ping($this->resourceId);
        }
        return false;
    }

    /**
     * Determines the version of the DBMS being used
     *
     * @return
     */
    public function getVersion(){
        return mysqli_get_server_info( $this->resourceId );
    }



    /**
     * Database object destructor
     *
     * @return boolean
     */
    final public function __destruct() {}
    
    
    
    
    final public function close(){
        $return = false;
        if (is_a($this->resourceId,"mysqli")) {
            $return = mysqli_close($this->resourceId);
        }
        $this->resourceId = NULL;
        return $return;
    }

    /**
     * Test to see if the MySQL connector is available
     *
     * @static
     * @access public
     * @return boolean  True on success, false otherwise.
     */
    final public function test() {
        return (function_exists('mysqli_connect'));
    }

    /**
     * Determines if the connection to the server is active.
     *
     * @access	public
     * @return	boolean
     */
    final public function connected() {
        if (is_a($this->resourceId,"mysqli")) {
            return mysqli_ping($this->resourceId);
        }
        return false;
    }

    /**
     * Determines UTF support
     *
     * @access	public
     * @return boolean True - UTF is supported
     */
    final public function hasUTF() {
        $verParts = explode('.', $this->getVersion());
        return ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int) $verParts[2] >= 2));
    }

    /**
     * Custom settings for UTF support
     *
     * @access	public
     */
    final public function setUTF() {
        mysqli_query($this->resourceId,"SET NAMES 'utf8'");
    }

    /**
     * Get a database escaped string
     *
     * @param	string	The string to be escaped
     * @param	boolean	Optional parameter to provide extra escaping
     * @return	string
     * @access	public
     * @abstract
     */
    final public function getEscaped($text, $extra = false) {
        $result = mysqli_real_escape_string($this->resourceId, $text);
        if ($extra) {
            $result = addcslashes($result, '%_');
        }
        return $result;
    }


    /**
     * Execute the query
     *
     * @access	public
     * @return mixed A database resource if successful, FALSE if not.
     */
    final public function exec( $query ='') {

        //@TODO how to verify the resource Id
        if (!is_a($this->resourceId, "mysqli")) {
            $this->setError( _t("No valid connection resource found") );
            return false;
        }

        // Take a local copy so that we don't modify the original query and cause issues later
        $sql = (empty($query)) ?  $this->query :  $query ;
        $this->query = $sql = $this->replacePrefix( $sql ); //just for reference

        if ($this->limit > 0 || $this->offset > 0) {
            $sql .= ' LIMIT ' . max($this->offset, 0) . ', ' . max($this->limit, 0);
        }

        if ($this->debug) {
            $this->ticker++;
            $this->log[] = $sql;
            $log = htmlentities($sql); //Does not play nice with the parser!
            \Platform\Debugger::log( $log, "DB Query {$this->ticker}" , "notice" );
        }

        $this->errorNum = 0;
        $this->errorMsg = '';
        $this->cursor = mysqli_query( $this->resourceId, $sql);

        if (!$this->cursor) {
            $this->errorNum = mysqli_errno($this->resourceId);
            $this->errorMsg = mysqli_error($this->resourceId) . " SQL=$sql";

            if ($this->debug) {
                //Debug the error
            }
            $this->setError( "[{$this->name}:{$this->errorNum}] {$this->errorMsg}");
            return false;
        }
        $this->resetRun();

        //echo $this->cursor;

        return $this->cursor;
    }


    /**
     * Prepares an SQL query for execution;
     *
     * @param string $statement
     * @return object \Library\Database\Results
     */
    final public function prepare($statement = NULL, $offset = 0, $limit = 0, $prefix='') {

        //Sets the query to be executed;

        $cfg = Config::getParamSection('database');


        $this->offset = (int) $offset;
        $this->limit = (int) $limit;
        $this->prefix = (!isset($prefix) && !empty($prefix)) ? $prefix : $cfg['prefix'];
        $this->query = $this->replacePrefix($statement);

        //Get the Result Statement class;
        $options = array(
            "dbo" => $this,
            "driver" => $this->driver
        );

        return new Statement( $this );
    }
    
        /**
     * Begins a database transaction
     * 
     * @return void;
     */
    public function startTransaction(){
        
        if (!is_a($this->resourceId, "mysqli")) {
            $this->setError( _t("No valid connection resource found") );
            return false;
        }
        $this->resourceId->autocommit( FALSE ); //Turns autocommit off;
        
    }
    
    /**
     * This method is intended for use in transsactions
     * 
     * @param type $sql
     * @param type $execute
     * @return boolean
     */
    public function query($sql, $execute = FALSE){
        
        $query = (empty($sql)) ?  $this->query :  $sql ;
        $this->transactions[] = $this->replacePrefix( $query ); //just for reference
        
        //@TODO;
        if($execute){
            $this->exec( $query );
        }
        
        return true;
    }
    
    /**
     * Commits a transaction or rollbacks on error
     * 
     * @return boolean
     */
    public function commitTransaction(){
         
        if(empty($this->transactions)||!is_array($this->transactions)){
            $this->setError(_t("No transaction queries found"));
            $this->transactions = array();
            $this->resourceId->autocommit( TRUE ); //Turns autocommit back on
            return false;
        }
        //Query transactions
        foreach($this->transactions as $query){
            if(!$this->exec($query)){
                $this->resourceId->rollback(); //Rolls back the transaction;
                $this->transactions = array();
                $this->resourceId->autocommit( TRUE ); //Turns autocommit back on
                return false;
            }
        }
        //Commit the transaction
        if(!$this->resourceId->commit()){
            $this->setError( _t("The transaction could not be committed"));
            $this->transactions = array();
            $this->resourceId->autocommit( TRUE ); //Turns autocommit back on
            return false;
        }
        
        $this->transactions = array();
        $this->resourceId->autocommit( TRUE ); //Turns autocommit back on
        return true;
    }

//    /**
//     * Gets an instance of the driver
//     *
//     * @staticvar self $instance
//     * @param array $options
//     * @return selfss
//     */
//    public static function getInstance( $options = array() ){
//
//
//        static $instance;
//        //If the class was already instantiated, just return it
//        if (isset($instance) && is_a($instance, "Library\Database\Drivers\MySQLi\Driver") ) return $instance ;
//
//        $instance =  new self($options);
//
//        return $instance;
//    }

    /**
     * For active record querying ONLY
     *
     * @param string $method
     * @param mixed $args
     * @return mixed
     */
    final public function __call($method, $args) {

//      Get the AR class;
//        $options = array(
//            "dbo" => $this,
//            "driver" => $this->driver
//        );

        $activeRecord = new Accessory($this);

//        $AR = \Library\Database\ActiveRecord::getInstance($options);
//

        if (!\method_exists($activeRecord, $method)) {
            $this->setError(_t('Method does not exists'));
            return false;
        }

        //Call the Method;
        return @\call_user_func_array(array($activeRecord, $method), $args);
    }
}