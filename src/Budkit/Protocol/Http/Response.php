<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 27/06/2014
 * Time: 03:59
 */

namespace Budkit\Protocol\Http;

use Budkit\Parameter\Factory as Parameters;
use Budkit\Parameter\Utility;
use Budkit\Protocol;

class Response implements Protocol\Response
{

    /**
     * All HTTP status codes are defined in this trait;
     */
    use Codes;

    use Protocol\Content;

    use Utility;

    protected $request;

    /**
     * @var \Symfony\Component\HttpFoundation\ResponseHeaderBag
     */
    protected $headers = null;

    /**
     * @var string
     */
    protected $content = [];


    protected $cookies;


    protected $contentType = null;
    /**
     * @var string
     */
    protected $version = "1.0";

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $statusText;

    /**
     * @var string
     */
    protected $charset;

    /**
     * If we are buffering the response content;
     *
     * @var string
     */
    protected $buffered = false;


    public function __construct($content = '', $status = 200, $options = [], Request $request)
    {

        $this->request = $request;

        $this->setStatusCode($status);

        if (!empty($options)) {
            if (isset($options['message'])) $this->setStatusMessage($options['message']);
            if (isset($options['version'])) $this->setProtocolVersion($options['version']);
            if (isset($options['charset'])) $this->setCharset($options['charset']);
        }

        $this->setHeaders(isset($options['headers']) ? $options['headers'] : []);
        $this->setCookies($this->request->getCookies());
        $this->addContent($content);
    }


    public function setStatusCode($code)
    {
        $this->statusCode = intval($code);

        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusMessage($message = '')
    {
        $this->statusText = $message;

        return $this;
    }

    public function getStatusMessage()
    {
        return $this->statusText;
    }


    public function setCharset($charset = 'utf-8')
    {
        $this->charset = $charset;

        return $this;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Sets the response cookies. Be warned that this replaces all cookies;
     *
     * @param string $cookies
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function setCookies($cookies = [])
    {

        $this->cookies = ($cookies instanceof Parameters) ? $cookies : new Parameters("cookies", (array)$cookies);

        return $this;
    }

    /**
     * Gets all defined cookies;
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function getCookies()
    {
        return $this->cookies;
    }


    protected function sendCookies()
    {
    }

    /**
     * Adds a cookie to the response;
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function addCookie($key, $value)
    {
        $this->cookies[$key] = $value;

        return $this;
    }

    /**
     * Removes a cookie from the response
     *
     * @param string $key
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function removeCookie($key)
    {
        $this->cookies->removeParameter($key);
    }

    /**
     * Gets the value of a response cookie;
     *
     * @param string $key
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function getCookie($key)
    {
        return $this->cookies[$key];
    }

    /**
     * Sets a HeaderBag containing all headers;
     *
     * @param string $headers
     *
     * @return Response
     * @author Livingstone Fultang
     */
    public function setHeaders(array $headers = [])
    {

        $this->headers = ($headers instanceof Headers) ? $headers : new Headers((array)$headers);

        return $this;

    }

    /**
     * Returns a list of all headers from the header bag
     *
     * @return Headers
     * @author Livingstone Fultang
     */
    public function getHeaders()
    {

        //Make sure we have some headers to return;
        if (!$this->headers) $this->setHeaders();

        return $this->headers;
    }

    /**
     * Add a header to the Header bag;
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function addHeader($key, $value = null)
    {
        //if loation change code to 320;
        $headers = $this->getHeaders();
        $headers->set($key, $value);

        return $this;
    }

    /**
     * Removes a header from the Header Bag
     *
     * @param string $key
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function removeHeader($key)
    {

        $headers = $this->getHeaders();

        return $headers->removeParameter($key);

    }

    /**
     * Gets a header defined in the header Bag;
     *
     * @param string $key
     * @param string $default
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function getHeader($key, $default = '')
    {

        $headers = $this->getheaders();

        return $headers->get($key, $default);

    }

    /**
     * Adds a content 'packet' to the content array;
     *
     * @param string $content
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function addContent($content = null)
    {

        if (!empty($content)) {
            $this->content[] = $content;
        }

        return $this;
    }

    /**
     * Gets content with specified Id, or all the content for buffering;
     *
     * @param string $packetId
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function getContent($packetId = null)
    {

        if (!isset($this->packetId)) {
            return $content = implode("/n", $this->content);
        }

        //Return the content with PacketId;
        return isset($this->content[$packetId]) ? $this->content[$packetId] : "";
    }


    public function setContentLength($length = 0)
    {

        $length = empty($length) ? $this->getContentLength() : (int)$length;

        $this->addHeader('Content-Length', $length);

        return $this;
    }

    public function getContentLength()
    {

        $content = $this->getContent(); // Not Ob_* content should already have been added to $content!;

        return strlen($content);
    }

    public function setContentType($type = null, $charset = "utf-8", $overite = false)
    {

        if ($this->headers->has("Content-Type") && !$overite) return $this; //If the header type has already been set;

        $this->setCharset($charset);

        $_charset = $this->getCharset();
        $_charset = !empty($_charset) ? "; charset=" . $_charset : null;
        $_type = $this->contentType = (is_null($type)) ? $this->getRequestFormat() : $type;

        //echo $this->contentType;

        if (($type = $this->getMimeType($_type)) !== false) {
            $this->addHeader("Content-Type", $type . $_charset);
        }

        return $this;
    }


    protected function getRequestFormat()
    {
        return $this->request->getAttribute("format", "html"); //@TODO change to a setting var for default contentType;
    }


    public function getContentType()
    {

        if (empty($this->contentType)) $this->setContentType();

        return $this->contentType;
    }

    protected function sendHeaders(array $headers = [])
    {

        $this->setContentLength();
        $this->setContentType();

        if (!headers_sent() && !$this->buffered) {

            if (!empty($headers)) {
                foreach ($headers as $header => $value) {
                    $this->addHeader($header, $value); //Will ovewrite any additional headers;
                }
            }
            //HTTP;
            header(sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $this->getStatusCode(),
                $this->getStatusMessage()), true, $this->getStatusCode());

            foreach ($this->headers->getAll() as $key => $values) {
                foreach ($values as $value) {
                    header($key . ': ' . $value, true, $this->getStatusCode());    //replace headers
                }
            }
        }

        return true;
    }


    public function setProtocolVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    public function getProtocolVersion()
    {
        return $this->version;
    }

    protected function sendContent($content = null)
    {

        //check we have all of these;
        //$this->setCookies();
        $this->addContent($content);

        $content = $this->getContent();


        //check we have a file;
        //check headers have already been sent;
        //send content;

        //Print content to screen;
        print($content);
    }

    /**
     * Sends headers + content to browser/console
     *
     * @return void
     * @author Livingstone Fultang
     */
    public function send($content = null)
    {


        //if not buffer sent
        if (!$this->buffered) {
            return $this->sendBuffer($content);
        }

        $this->sendContent($content);

        return $this;
    }

    public function sendRedirect($headers = [])
    {

        $this->sendHeaders($headers);

        return;
    }

    public function getDataArray()
    {
        return $this->getAllParameters();
    }

    public function setDataArray(array $data)
    {
        foreach ($data as $key => $value) {
            $this->setData($key, $value);
        }

        return $this;
    }

    public function setData($key, $value = '')
    {
        return $this->setParameter($key, $value);
    }

    public function getData($key)
    {
        return $this->getParameter($key);
    }

    public function addAlert($message, $messageType = "info")
    {
        $this->addParameters(["alerts" => [["message" => strval($message), "type" => strval($messageType)]]]);

    }

    public function getAlerts()
    {

        $alerts = $this->getData("alerts");

        return empty($alerts) ? [] : $alerts;

    }


    protected function sendBuffer($content = null, $headers = [])
    {

        $this->sendCookies();

        //Are we sending any more headers with the buffer?
        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                $this->addHeader($key, $value);
            }
        }

        //Now send the headers
        $this->sendHeaders();

        //Attache the content;
        $this->sendContent($content);

        //Buffered
        $this->buffered = true;

        //var_dump($this);
    }

}