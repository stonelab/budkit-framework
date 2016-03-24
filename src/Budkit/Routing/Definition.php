<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 03/07/2014
 * Time: 03:36
 */

namespace Budkit\Routing;


use Budkit\Protocol\Request;


abstract class Definition
{

    /**
     *
     * The route failed to match at isRoutableMatch().
     *
     * @const string
     *
     */
    const FAILED_ROUTABLE = 'FAILED_ROUTABLE';

    /**
     *
     * The route failed to match at isSecureMatch().
     *
     * @const string
     *
     */
    const FAILED_SECURE = 'FAILED_SECURE';

    /**
     *
     * The route failed to match at isRegexMatch().
     *
     * @const string
     *
     */
    const FAILED_REGEX = 'FAILED_REGEX';

    /**
     *
     * The route failed to match at isMethodMatch().
     *
     * @const string
     *
     */
    const FAILED_METHOD = 'FAILED_METHOD';

    /**
     *
     * The route failed to match at isAcceptMatch().
     *
     * @const string
     *
     */
    const FAILED_ACCEPT = 'FAILED_ACCEPT';

    /**
     *
     * The route failed to match at isServerMatch().
     *
     * @const string
     *
     */
    const FAILED_SERVER = 'FAILED_SERVER';

    /**
     *
     * The route failed to match at isCustomMatch().
     *
     * @const string
     *
     */
    const FAILED_CUSTOM = 'FAILED_CUSTOM';


    protected $tokens = [];
    protected $server = [];
    /**
     *
     * HTTP method(s).
     *
     * @var array
     *
     */
    protected $method = [];

    /**
     *
     * Accept header values.
     *
     * @var array
     *
     */
    protected $accept = [];
    protected $values = [];
    protected $permissions = [];
    protected $requiredPermission = 'view'; //the minimum permission required for this route;
    protected $secure = null; //false = must not be secure, true = must be secure, null = does not matter
    protected $wildcard = null;
    protected $routable = true;
    protected $action = null;
    protected $generate = null;
    protected $stateless = false;

    /**
     *
     * Custom callable for isMatch() logic.
     *
     * @var callable
     *
     */
    protected $isMatch = null;


    /**
     *
     * Debugging information about why the route did not match.
     *
     * @var array
     *
     */
    protected $debug;

    /**
     *
     * Magic read-only for all properties and spec keys.
     *
     * @param string $key The property to read from.
     *
     * @return mixed
     *
     */
    public function __get($key)
    {
        return $this->$key;
    }

    /**
     *
     * Magic isset() for all properties.
     *
     * @param string $key The property to check if isset().
     *
     * @return bool
     *
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }


    /**
     * Sets tokens to the Route definition, replacing all existing
     *
     * @param array $tokens
     *
     * @return $this
     */
    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;

        return $this;
    }

    /**
     *
     * Merges with the existing regular expressions for param tokens.
     *
     * @param array $tokens Regular expressions for param tokens.
     *
     * @return $this
     *
     */
    public function addTokens(array $tokens)
    {
        $this->tokens = array_merge($this->tokens, $tokens);
        $this->regex = null;

        return $this;
    }

    /**
     *
     * Sets the regular expressions for server values.
     *
     * @param array $server The regular expressions for server values.
     *
     * @return $this
     *
     */
    public function setServer(array $server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     *
     * Merges with the existing regular expressions for server values.
     *
     * @param array $server Regular expressions for server values.
     *
     * @return $this
     *
     */
    public function addServer(array $server)
    {
        $this->server = array_merge($this->server, $server);
        $this->regex = null;

        return $this;
    }

    /**
     *
     * Sets the default values for params.
     *
     * @param array $values Default values for params.
     *
     * @return $this
     *
     */
    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     *
     * Merges with the existing default values for params.
     *
     * @param array $values Default values for params.
     *
     * @return $this
     *
     */
    public function addValues(array $values)
    {
        $this->values = array_merge($this->values, $values);

        return $this;
    }

    /**
     *
     * Sets whether or not the route must be secure.
     *
     * @param bool $secure If true, the server must indicate an HTTPS request;
     *                     if false, it must *not* be HTTPS; if null, it doesn't matter.
     *
     * @return $this
     *
     */
    public function setSecure($secure = true)
    {
        $this->secure = ($secure === null) ? null : (bool)$secure;

        return $this;
    }

    /**
     *
     * Sets the name of the wildcard param.
     *
     * @param string $wildcard The name of the wildcard param, if any.
     *
     * @return $this
     *
     */
    public function setWildcard($wildcard)
    {
        $this->wildcard = $wildcard;

        return $this;
    }

    /**
     *
     * Sets whether or not this route should be used for matching.
     *
     * @param bool $routable If true, this route can be matched; if not, it
     *                       can be used only to generate a path.
     *
     * @return $this
     *
     */
    public function setRoutable($routable = true)
    {
        $this->routable = (bool)$routable;

        return $this;
    }


    /**
     * Set the route action controller
     *
     * @param $actionController
     * @return $this
     */
    public function setAction($actionController)
    {
        $this->action = $actionController;

        return $this;
    }

    /**
     *
     * Defines a callback with which to check for a particular permission
     *
     * @param string $for the permission e.g view, modify, execute, special
     * @param string $withCallback the custom callable to use
     * @return $this
     *
     */
    public function setPermissionHandler($for, $withCallback){

        if(!isset($this->permissions) || !is_array($this->permissions)){
            $this->permissions = [];
        }

        $this->permissions = array_merge($this->permissions, [$for => $withCallback ]);

        return $this;
    }


    /**
     *
     * @param $permission
     * @return $this|bool
     */
    public function setRequiredPermission($permission){

        $permissions = ["view","modify","execute","special"];

        //We can only accept certain types of permissions
        if(!in_array($permission, $permissions)) return false;

        $this->requiredPermission = strtolower($permission);


        return $this;
    }


    public function setIsStateless(){

        $this->stateless = true;

        return $this;

    }


    public function isStateless(){
        return $this->stateless;
    }

    /**
     *
     * Sets a custom callable to evaluate the route for matching.
     *
     * @param callable $is_match A custom callable to evaluate the route.
     *
     * @return $this
     *
     */
    public function setIsMatchCallable($isMatch)
    {
        $this->isMatch = $isMatch;

        return $this;
    }


    /**
     *
     * Sets a custom callable to modify data for `generate()`.
     *
     * @param callable $generate A custom callable to modify data for
     *                           `generate()`.
     *
     * @return $this
     *
     */
    public function setGenerateCallable($generate)
    {
        $this->generate = $generate;

        return $this;
    }

    /**
     *
     * Sets the allowable method(s), overwriting previous the previous value.
     *
     * @param string|array $method The allowable method(s).
     *
     * @return $this
     *
     */
    public function setMethod($method)
    {
        $this->method = (array)$method;

        return $this;
    }

    /**
     *
     * Adds to the allowable method(s).
     *
     * @param string|array $method The allowable method(s).
     *
     * @return $this
     *
     */
    public function addMethod($method)
    {
        $this->method = array_merge($this->method, (array)$method);

        return $this;
    }


    /**
     *
     * Sets the list of matchable content-types, overwriting previous values.
     *
     * @param string|array $accept The matchable content-types.
     *
     * @return $this
     *
     */
    public function setAccept($accept)
    {
        $this->accept = (array)$accept;

        return $this;
    }

    /**
     *
     * Adds to the list of matchable content-types.
     *
     * @param string|array $accept The matchable content-types.
     *
     * @return $this
     *
     */
    public function addAccept($accept)
    {
        $this->accept = array_merge($this->accept, (array)$accept);

        return $this;
    }


    public function matches(Request $request, $strict = true)
    {

        $this->debug = [];
        $this->params = [];
        $this->score = 0;
        $this->failed = null;

        if ($this->isMatch($request->getPathInfo(), $request->getServer())) {
            $this->setParams();

            return true;
        }

        //If no match store reason for failure in debug var
        return false;
    }

} 