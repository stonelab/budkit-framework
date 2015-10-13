<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 03/07/2014
 * Time: 02:22
 */

namespace Budkit\Routing;

use ArrayObject;
use Budkit\Protocol\Request;
use Budkit\Protocol\Server;


class Route extends Definition
{


    /**
     *
     * The name for this Route.
     *
     * @var string
     *
     */
    protected $name;

    /**
     *
     * The path for this Route with param tokens.
     *
     * @var string
     *
     */
    protected $path;


    /**
     *
     * The `$path` property converted to a regular expression, using the
     * `$tokens` subpatterns.
     *
     * @var string
     *
     */
    protected $regex;

    /**
     *
     * Matched param values.
     *
     * @var array
     *
     */
    protected $params = [];

    /**
     *
     * All params found during the `isMatch()` process, both from the path
     * tokens and from matched server values.
     *
     * @var array
     * @see isMatch()
     *
     */
    protected $matches = [];


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
     * The matching score for this route (+1 for each is*Match() that passes).
     *
     * @var int
     *
     */
    protected $score = 0;

    /**
     *
     * The failure code, if any, during matching.
     *
     * @var string
     *
     */
    protected $failed = null;


    public function __construct($path, $name = null, array $parameters = [])
    {

        $this->name = $name;
        $this->path = $path;
        $this->params = $parameters;

        return $this;
    }

    /**
     *
     * Check whether a failure happened due to accept header
     *
     * @return bool
     *
     */
    public function failedAccept()
    {
        return $this->failed == self::FAILED_ACCEPT;
    }

    /**
     *
     * Check whether a failure happened due to http method
     *
     * @return bool
     *
     */
    public function failedMethod()
    {
        return $this->failed == self::FAILED_METHOD;
    }

    public function setParam($key, $value, $replace = true)
    {

        if (isset($this->params[$key]) && !$replace) {
            return $this;
        }
        $this->params[$key] = $value;

        return $this;
    }

    /**
     *
     * Is the route a full match?
     *
     * @param string $path The path to check against this route
     *
     * @param array $server A copy of $_SERVER so that this Route can check
     *                       against the server values.
     *
     * @return bool
     *
     */
    protected function isMatch($path, Server $server)
    {
        return $this->isRoutableMatch()
        && $this->isSecureMatch($server)
        && $this->isRegexMatch($path)
        && $this->isMethodMatch($server)
        && $this->isAcceptMatch($server)
        && $this->isServerMatch($server)
        && $this->isCustomMatch($server);
    }

    /**
     *
     * Check whether a failure happened due to route not match
     *
     * @return bool
     *
     */
    protected function isRoutableMatch()
    {
        if ($this->routable) {
            return $this->pass();
        }

        return $this->fail(self::FAILED_ROUTABLE);
    }

    /**
     *
     * A partial match passed.
     *
     * @return bool
     *
     */
    protected function pass()
    {
        $this->score++;

        return true;
    }

    /**
     *
     * A partial match failed.
     *
     * @param string $failed The reason of failure
     *
     * @param string $append
     *
     * @return bool
     *
     */
    protected function fail($failed, $append = null)
    {
        $this->debug[] = $failed . $append;
        $this->failed = $failed;

        return false;
    }


    /**
     *
     * Determines if this is route matches the request path
     * without setting any params.
     *
     * @param Request $request
     * @return bool
     */
    public function isRequestMatch(Request $request)
    {

        if ($this->isMatch($request->getPathInfo(), $request->getServer())) {
            return true;
        }

        return false;
    }


    /**
     * Returns the path in the route definition
     *
     * @return string
     */
    public function getPath()
    {

        return $this->path;
    }


    /**
     * Returns the name in the route definition
     *
     * @return string
     */
    public function getName()
    {

        return $this->name;

    }


    /**
     *
     * Checks that the Route `$secure` matches the corresponding server values.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return bool True on a match, false if not.
     *
     */
    protected function isSecureMatch($server)
    {
        if ($this->secure === null) {
            return $this->pass();
        }

        if ($this->secure != $this->serverIsSecure($server)) {
            return $this->fail(self::FAILED_SECURE);
        }

        return $this->pass();
    }

    /**
     *
     * Check whether the server is in secure mode
     *
     * @param array $server
     *
     * @return bool
     *
     */
    protected function serverIsSecure($server)
    {
        return (isset($server['HTTPS']) && $server['HTTPS'] == 'on')
        || (isset($server['SERVER_PORT']) && $server['SERVER_PORT'] == 443);
    }

    /**
     *
     * Checks that the path matches the Route regex.
     *
     * @param string $path The path to match against.
     *
     * @return bool True on a match, false if not.
     *
     */
    protected function isRegexMatch($path)
    {
        $regex = new Regex;

        $match = $regex->match($this, $path);

        $this->regex = $regex->getRegexPath();

        if (!$match) {
            return $this->fail(self::FAILED_REGEX);
        }
        $this->matches = new ArrayObject($regex->getMatches());

        return $this->pass();
    }

    /**
     *
     * Is the requested method matching
     *
     * @param array $server
     *
     * @return bool
     *
     */
    protected function isMethodMatch($server)
    {
        if (!$this->method) {
            return $this->pass();
        }

        $pass = isset($server['REQUEST_METHOD'])
            && in_array($server['REQUEST_METHOD'], $this->method);

        return $pass
            ? $this->pass()
            : $this->fail(self::FAILED_METHOD);
    }

    /**
     *
     * Is the Accept header a match.
     *
     * @param array $server
     *
     * @return bool
     *
     */
    protected function isAcceptMatch($server)
    {
        if (!$this->accept || !isset($server['HTTP_ACCEPT'])) {
            return $this->pass();
        }

        $header = str_replace(' ', '', $server['HTTP_ACCEPT']);

        if ($this->isAcceptMatchHeader('*/*', $header)) {
            return $this->pass();
        }

        foreach ($this->accept as $type) {
            if ($this->isAcceptMatchHeader($type, $header)) {
                return $this->pass();
            }
        }

        return $this->fail(self::FAILED_ACCEPT);
    }

    /**
     *
     * Is the accept method matching
     *
     * @param string $type
     *
     * @param string $header
     *
     * @return bool
     *
     */
    protected function isAcceptMatchHeader($type, $header)
    {
        list($type, $subtype) = explode('/', $type);
        $type = preg_quote($type);
        $subtype = preg_quote($subtype);
        $regex = "#$type/($subtype|\*)(;q=(\d\.\d))?#";
        $found = preg_match($regex, $header, $matches);
        if (!$found) {
            return false;
        }

        return isset($matches[3]) && $matches[3] !== '0.0';
    }

    /**
     *
     * Checks that $_SERVER values match their related regular expressions.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return bool True if they all match, false if not.
     *
     */
    protected function isServerMatch($server)
    {
        foreach ($this->server as $name => $regex) {
            $matches = $this->isServerMatchRegex($server, $name, $regex);
            if (!$matches) {
                return $this->fail(self::FAILED_SERVER, " ($name)");
            }
            $this->matches[$name] = $matches[$name];
        }

        return $this->pass();
    }

    /**
     *
     * Does a server key match a regex?
     *
     * @param array $server The server values.
     *
     * @param string $name The server key.
     *
     * @param string $regex The regex to match against.
     *
     * @return array
     *
     */
    protected function isServerMatchRegex($server, $name, $regex)
    {
        $value = isset($server[$name])
            ? $server[$name]
            : '';
        $regex = "#(?P<{$name}>{$regex})#";
        preg_match($regex, $value, $matches);

        return $matches;
    }

    /**
     *
     * Checks that the custom Route `$isMatch` callable returns true, given
     * the server values.
     *
     * @param array $server A copy of $_SERVER.
     *
     * @return bool True on a match, false if not.
     *
     */
    protected function isCustomMatch($server)
    {
        if (!$this->isMatch) {
            return $this->pass();
        }

        // attempt the match
        $result = call_user_func($this->isMatch, $server, $this->matches);

        // did it match?
        if (!$result) {
            return $this->fail(self::FAILED_CUSTOM);
        }

        return $this->pass();
    }

    /**
     *
     * Sets the route params from the matched values.
     *
     * @return null
     *
     */
    protected function setParams()
    {
        $this->params = $this->values;
        $this->setParamsWithMatches();
        $this->setParamsWithWildcard();

    }

    /**
     *
     * Set the params with their matched values.
     *
     * @return null
     *
     */
    protected function setParamsWithMatches()
    {
        // populate the path matches into the route values. if the path match
        // is exactly an empty string, treat it as missing/unset. (this is
        // to support optional ".format" param values.)
        foreach ($this->matches as $key => $val) {
            if (is_string($key) && $val !== '') {
                $this->params[$key] = rawurldecode($val);
            }
        }
    }

    /**
     *
     * Set the wildcard param value.
     *
     * @return null
     *
     */
    protected function setParamsWithWildcard()
    {
        if (!$this->wildcard) {
            return;
        }

        if (empty($this->params[$this->wildcard])) {
            $this->params[$this->wildcard] = [];

            return;
        }

        $this->params[$this->wildcard] = array_map(
            'rawurldecode',
            explode('/', $this->params[$this->wildcard])
        );
    }
} 