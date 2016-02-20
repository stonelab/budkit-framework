<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 27/06/2014
 * Time: 03:58
 */

namespace Budkit\Protocol\Http;

use Budkit\Parameter\Factory as Parameters;
use Budkit\Protocol;
use Budkit\Session;

class Request implements Protocol\Request
{

    /**
     * @var array
     */
    protected static $formats;
    protected $get = [];
    protected $post = [];
    protected $cookies = [];
    protected $files = [];
    protected $server = [];
    protected $headers = [];
    /**
     * @var string
     */
    protected $content;
    /**
     * @var array
     */
    protected $languages;
    /**
     * @var array
     */
    protected $charsets;
    /**
     * @var array
     */
    protected $encodings;
    /**
     * @var array
     */
    protected $acceptableContentTypes;
    /**
     * @var string
     */
    protected $pathInfo;
    /**
     * @var string
     */
    protected $requestUri;
    /**
     * @var string
     */
    protected $baseUrl;
    /**
     * @var string
     */
    protected $basePath;
    /**
     * @var string
     */
    protected $method;
    /**
     * @var string
     */
    protected $format;
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;
    /**
     * @var string
     */
    protected $locale;
    /**
     * @var string
     */
    protected $defaultLocale = 'en';

    /**
     * Create a new request passing the request parameters;
     *
     * @param array $query
     * @param array $request
     * @param array $attributes
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param null $content
     */
    public function __construct(array $query = [], array $data = [], array $attributes = [], array $cookies = [],
                                array $files = [], array $server = [], $content = null)
    {

        $this->get = new Parameters("get", $query); //stores all the get parameters;
        $this->post = new Parameters("post", $data, false); //stores all the post data params = get+post+cookie params;

        $this->attributes = new Parameters("attributes", $attributes);
        $this->cookies = new Parameters("cookies", $cookies); //stores all the cookies;
        $this->files = new Parameters("files", $files, false);

        $this->server = new Server($server);
        $this->headers = new Headers($this->server->getHeaders());

        $this->content = $content;
        $this->languages = null;
        $this->charsets = null;
        $this->encodings = null;
        $this->acceptableContentTypes = null;
        $this->pathInfo = null;
        $this->requestUri = null;
        $this->baseUrl = null;
        $this->basePath = null;
        $this->method = null;
        $this->format = null;
        $this->session = null;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes($attributes = [])
    {
        if ($attributes instanceof Parameters) {
            return $this->attributes = $attributes;
        }

        if (is_array($attributes)) {

            $this->get->addParameters($attributes);

            return $this->attributes->addParameters($attributes);
        }

    }

    public function getAttribute($key, $default = null)
    {

        return $this->attributes->getParameter($key, $default);

    }

    public function getSession()
    {
        return $this->session;
    }

    public function setSession(Session\Handler $session)
    {
        $this->session = $session;
    }

    public function hasPreviousSession()
    {
        // the check for $this->session avoids malicious users trying to fake a session cookie with proper name
        return $this->hasSession() && $this->cookies->hasParameter($this->session->getName());
    }

    public function hasSession()
    {
        return null !== $this->session;
    }

    public function getScriptName()
    {
        return ($this->server->hasParameter('SCRIPT_NAME')) ? $this->server->getParameter('SCRIPT_NAME')
            : $this->server->getParameter('ORIG_SCRIPT_NAME');
    }

    public function getBasePath()
    {
        if (null === $this->basePath) {
            $this->basePath = $this->prepareBasePath();
        }

        return $this->basePath;
    }

    protected function prepareBasePath()
    {
        $filename = basename($this->server->getParameter('SCRIPT_FILENAME'));
        $baseUrl = $this->getBaseUrl();
        if (empty($baseUrl)) {
            return '';
        }

        if (basename($baseUrl) === $filename) {
            $basePath = dirname($baseUrl);
        } else {
            $basePath = $baseUrl;
        }

        if ('\\' === DIRECTORY_SEPARATOR) {
            $basePath = str_replace('\\', '/', $basePath);
        }

        return rtrim($basePath, '/');
    }

    public function getBaseUrl()
    {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->prepareBaseUrl();
        }

        return $this->baseUrl;
    }

    protected function prepareBaseUrl()
    {
        $filename = basename($this->server->getParameter('SCRIPT_FILENAME'));

        if (basename($this->server->getParameter('SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->getParameter('SCRIPT_NAME');
        } elseif (basename($this->server->getParameter('PHP_SELF')) === $filename) {
            $baseUrl = $this->server->getParameter('PHP_SELF');
        } elseif (basename($this->server->getParameter('ORIG_SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->getParameter('ORIG_SCRIPT_NAME'); // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = $this->server->getParameter('PHP_SELF', '');
            $file = $this->server->getParameter('SCRIPT_FILENAME', '');
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/' . $seg . $baseUrl;
                ++$index;
            } while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri();

        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $prefix;
        }

        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, dirname($baseUrl))) {
            // directory portion of $baseUrl matches
            return rtrim($prefix, '/');
        }

        $truncatedRequestUri = $requestUri;
        if (false !== $pos = strpos($requestUri, '?')) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if (strlen($requestUri) >= strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && $pos !== 0) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return rtrim($baseUrl, '/');
    }

    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    protected function prepareRequestUri()
    {

        $requestUri = '';

        if ($this->headers->hasParameter('X_ORIGINAL_URL')) {
            // IIS with Microsoft Rewrite Module
            $requestUri = $this->headers->getParameter('X_ORIGINAL_URL');
            $this->headers->removeParameter('X_ORIGINAL_URL');
            $this->server->removeParameter('HTTP_X_ORIGINAL_URL');
            $this->server->removeParameter('UNENCODED_URL');
            $this->server->removeParameter('IIS_WasUrlRewritten');
        } elseif ($this->headers->hasParameter('X_REWRITE_URL')) {
            // IIS with ISAPI_Rewrite
            $requestUri = $this->headers->getParameter('X_REWRITE_URL');
            $this->headers->removeParameter('X_REWRITE_URL');
        } elseif ($this->server->getParameter('IIS_WasUrlRewritten') == '1'
            && $this->server->getParameter('UNENCODED_URL') != ''
        ) {
            // IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
            $requestUri = $this->server->getParameter('UNENCODED_URL');
            $this->server->removeParameter('UNENCODED_URL');
            $this->server->removeParameter('IIS_WasUrlRewritten');
        } elseif ($this->server->hasParameter('REQUEST_URI')) {
            $requestUri = $this->server->getParameter('REQUEST_URI');
            // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path, only use URL path
            $schemeAndHttpHost = $this->getSchemeAndHttpHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        } elseif ($this->server->hasParameter('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = $this->server->getParameter('ORIG_PATH_INFO');
            if ('' != $this->server->getParameter('QUERY_STRING')) {
                $requestUri .= '?' . $this->server->getParameter('QUERY_STRING');
            }
            $this->server->removeParameter('ORIG_PATH_INFO');
        }

        // normalize the request URI to ease creating sub-requests from this request
        $this->server->setParameter('REQUEST_URI', $requestUri);

        return $requestUri;
    }

    public function getSchemeAndHttpHost()
    {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }

    public function getScheme()
    {

        $schemeOn =
            'on' == strtolower($this->server->getParameter('HTTPS')) || 1 == $this->server->getParameter('HTTPS');

        return $schemeOn ? "https" : "http";
    }

    public function getHttpHost()
    {
        $scheme = $this->getScheme();
        $port = $this->getPort();

        if (('http' == $scheme && $port == 80) || ('https' == $scheme && $port == 443)) {
            return $this->getHost();
        }

        return $this->getHost() . ':' . $port;
    }

    public function getPort()
    {

        if ($host = $this->headers->getParameter('HOST')) {
            if (false !== $pos = strrpos($host, ':')) {
                return intval(substr($host, $pos + 1));
            }

            return 'https' === $this->getScheme() ? 443 : 80;
        }

        return $this->server->getParameter('SERVER_PORT');
    }

    public function getHost()
    {
        if (!$host = $this->headers->getParameter('HOST')) {
            if (!$host = $this->server->getParameter('SERVER_NAME')) {
                $host = $this->server->getParameter('SERVER_ADDR', '');
            }
        }

        //need to check if this is empty and throw an error. Malformed request maybe.

        // trim and remove port number from host
        // host is lowercase as per RFC 952/2181
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

        // as the host can come from the user (HTTP_HOST and depending on the configuration, SERVER_NAME too can come from the user)
        // check that it does not contain forbidden characters (see RFC 952 and RFC 2181)
        if ($host && !preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $host)) {
            throw new \UnexpectedValueException(sprintf('Invalid Host "%s"', $host));
        }

        return $host;
    }

    private function getUrlencodedPrefix($string, $prefix)
    {
        if (0 !== strpos(rawurldecode($string), $prefix)) {
            return false;
        }

        $len = strlen($prefix);

        if (preg_match("#^(%[[:xdigit:]]{2}|.){{$len}}#", $string, $match)) {
            return $match[0];
        }

        return false;
    }

    public function getProtocol()
    {
    }

    public function getResponse()
    {
    }

    public function send(Protocol\Request $request = null)
    {
    }

    public function getUri()
    {
        if (null !== $qs = $this->getQueryString()) {
            $qs = '?' . $qs;
        }

        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $this->getPathInfo() . $qs;
    }


    function getMethodData($method = "get")
    {

        if (isset($this->$method)) {
            if (is_a($this->$method, Parameters::class)) {
                $parameters = $this->$method;
                return $parameters->getAllParameters();
            }
        }

        return false;
    }

    public function getQueryString()
    {
        $qs = static::normalizeQueryString($this->server->getParameter('QUERY_STRING'));

        return '' === $qs ? null : $qs;
    }

    public static function normalizeQueryString($qs)
    {
        if ('' == $qs) {
            return '';
        }

        $parts = [];
        $order = [];

        foreach (explode('&', $qs) as $param) {
            if ('' === $param || '=' === $param[0]) {
                // Ignore useless delimiters, e.g. "x=y&".
                // Also ignore pairs with empty key, even if there was a value, e.g. "=value", as such nameless values cannot be retrieved anyway.
                // PHP also does not include them when building _GET.
                continue;
            }

            $keyValuePair = explode('=', $param, 2);

            // GET parameters, that are submitted from a HTML form, encode spaces as "+" by default (as defined in enctype application/x-www-form-urlencoded).
            // PHP also converts "+" to spaces when filling the global _GET or when using the function parse_str. This is why we use urldecode and then normalize to
            // RFC 3986 with rawurlencode.
            $parts[] = isset($keyValuePair[1])
                ?
                rawurlencode(urldecode($keyValuePair[0])) . '=' . rawurlencode(urldecode($keyValuePair[1]))
                :
                rawurlencode(urldecode($keyValuePair[0]));
            $order[] = urldecode($keyValuePair[0]);
        }

        array_multisort($order, SORT_ASC, $parts);

        return implode('&', $parts);
    }

    public function getPathInfo()
    {
        if (null === $this->pathInfo) {
            $this->pathInfo = $this->preparePathInfo();
        }

        return $this->pathInfo;
    }

    protected function preparePathInfo()
    {
        $baseUrl = $this->getBaseUrl();

        if (null === ($requestUri = $this->getRequestUri())) {
            return '/';
        }

        $pathInfo = '/';

        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if (null !== $baseUrl && false === $pathInfo = substr($requestUri, strlen($baseUrl))) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        } elseif (null === $baseUrl) {
            return $requestUri;
        }

        return (string)$pathInfo;
    }

    public function getUriForPath($path)
    {
        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $path;
    }

    public function getMimeType($format)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }

        return isset(static::$formats[$format]) ? static::$formats[$format][0] : null;
    }

    protected static function initializeFormats()
    {
        static::$formats = [
            'html' => ['text/html', 'application/xhtml+xml'],
            'txt' => ['text/plain'],
            'js' => ['application/javascript', 'application/x-javascript', 'text/javascript'],
            'css' => ['text/css'],
            'json' => ['application/json', 'application/x-json'],
            'xml' => ['text/xml', 'application/xml', 'application/x-xml'],
            'rdf' => ['application/rdf+xml'],
            'atom' => ['application/atom+xml'],
            'rss' => ['application/rss+xml'],
        ];
    }

    public function getFormat($mimeType)
    {
        if (false !== $pos = strpos($mimeType, ';')) {
            $mimeType = substr($mimeType, 0, $pos);
        }

        if (null === static::$formats) {
            static::initializeFormats();
        }

        foreach (static::$formats as $format => $mimeTypes) {
            if (in_array($mimeType, (array)$mimeTypes)) {
                return $format;
            }
        }
    }

    public function setFormat($format, $mimeTypes)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }
        static::$formats[$format] = is_array($mimeTypes) ? $mimeTypes : [$mimeTypes];
    }

    public function getRequestFormat($default = 'html')
    {
        if (null === $this->format) {
            $this->format = $this->get('_format', $default); //lookup in $_REQUEST
        }

        return $this->format;
    }

    public function setRequestFormat($format)
    {
        $this->format = $format;
    }

    public function getMethod()
    {
        if (null === $this->method) {
            $this->method = strtoupper($this->server->getParameter('REQUEST_METHOD', 'GET'));

            if ('POST' === $this->method) {
                if ($method = $this->headers->getParameter('X-HTTP-METHOD-OVERRIDE')) {
                    $this->method = strtoupper($method);
                }
            }
        }

        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = null;
        $this->server->setParameter('REQUEST_METHOD', $method);
    }

    public function getQuery()
    {
        return $this->get;
    }

    public function getData()
    {
        return $this->post;
    }

    public function getCookies()
    {
        return $this->cookies;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns the request body content.
     *
     * @param bool $asResource If true, a resource will be returned
     *
     * @return string|resource The request body content or a resource to read the body stream.
     *
     * @throws \LogicException
     */
    public function getContent($asResource = false)
    {
        if (false === $this->content || (true === $asResource && null !== $this->content)) {
            throw new \LogicException('getContent() can only be called once when using the resource return type.');
        }

        if (true === $asResource) {
            $this->content = false;

            return fopen('php://input', 'rb');
        }

        if (null === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    /**
     * Gets the Etags.
     *
     * @return array The entity tags
     */
    public function getETags()
    {
        return preg_split('/\s*,\s*/', $this->headers->getParameter('if_none_match'), null, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @return bool
     */
    public function isNoCache()
    {
        return $this->headers->hasCacheControlDirective('no-cache')
        || 'no-cache' == $this->headers->getParameter('Pragma');
    }

    /**
     * Returns the preferred language.
     *
     * @param array $locales An array of ordered available locales
     *
     * @return string|null The preferred locale
     *
     * @api
     */
    public function getPreferredLanguage(array $locales = null)
    {
        $preferredLanguages = $this->getLanguages();

        if (empty($locales)) {
            return isset($preferredLanguages[0]) ? $preferredLanguages[0] : null;
        }

        if (!$preferredLanguages) {
            return $locales[0];
        }

        $extendedPreferredLanguages = [];
        foreach ($preferredLanguages as $language) {
            $extendedPreferredLanguages[] = $language;
            if (false !== $position = strpos($language, '_')) {
                $superLanguage = substr($language, 0, $position);
                if (!in_array($superLanguage, $preferredLanguages)) {
                    $extendedPreferredLanguages[] = $superLanguage;
                }
            }
        }

        $preferredLanguages = array_values(array_intersect($extendedPreferredLanguages, $locales));

        return isset($preferredLanguages[0]) ? $preferredLanguages[0] : $locales[0];
    }

    /**
     * Gets a list of languages acceptable by the client browser.
     *
     * @return array Languages ordered in the user browser preferences
     *
     * @api
     */
    public function getLanguages()
    {
        if (null !== $this->languages) {
            return $this->languages;
        }

        $languages = $this->headers->getParameterListAsObject('Accept-Language');
        //@TODO convert comma seperated list to Array
        $this->languages = [];

        foreach (array_keys($languages) as $lang) {
            if (strstr($lang, '-')) {
                $codes = explode('-', $lang);
                if ($codes[0] == 'i') {
                    // Language not listed in ISO 639 that are not variants
                    // of any listed language, which can be registered with the
                    // i-prefix, such as i-cherokee
                    if (count($codes) > 1) {
                        $lang = $codes[1];
                    }
                } else {
                    for ($i = 0, $max = count($codes); $i < $max; $i++) {
                        if ($i == 0) {
                            $lang = strtolower($codes[0]);
                        } else {
                            $lang .= '_' . strtoupper($codes[$i]);
                        }
                    }
                }
            }

            $this->languages[] = $lang;
        }

        return $this->languages;
    }

    /**
     * Gets a list of charsets acceptable by the client browser.
     *
     * @return array List of charsets in preferable order
     *
     * @api
     */
    public function getCharsets()
    {
        if (null !== $this->charsets) {
            return $this->charsets;
        }

        $charsets = $this->headers->getParameterListAsObject('Accept-Charset');

        return $this->charsets = $charsets->getParameterKeys();
    }

    /**
     * Gets a list of encodings acceptable by the client browser.
     *
     * @return array List of encodings in preferable order
     */
    public function getEncodings()
    {
        if (null !== $this->encodings) {
            return $this->encodings;
        }
        $charsets = $this->headers->getParameterListAsObject('ACCEPT_ENCODINGS');

        return $this->encodings = $encodings->getParameterKeys();
    }

    /**
     * Gets a list of content types acceptable by the client browser
     *
     * @return array List of content types in preferable order
     *
     * @api
     */
    public function getAcceptableContentTypes()
    {
        if (null !== $this->acceptableContentTypes) {
            return $this->acceptableContentTypes;
        }

        $acceptableContentTypes = $this->headers->getParameterListAsObject('ACCEPT');

        return $this->acceptableContentTypes = $acceptableContentTypes->getParameterKeys();
    }

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * It works if your JavaScript library set an X-Requested-With HTTP header.
     * It is known to work with common JavaScript frameworks:
     *
     * @link http://en.wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
     *
     * @return bool    true if the request is an XMLHttpRequest, false otherwise
     *
     * @api
     */
    public function isXmlHttpRequest()
    {
        return 'XMLHttpRequest' == $this->headers->getParameter('X-Requested-With');
    } //gets the response from a send Request;

} 