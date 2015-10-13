<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * uri.php
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
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 */

namespace Budkit\Protocol;

use Budkit\Datastore\Encrypt;
use Budkit\Validation\Validate;

/**
 * A library providing URI and URL parsing capability
 *
 * The main purpose of this class is to automatically determine the key components
 * pertaining to identifying the requested resource as well as build resource identifiers
 * to system resources and actions. Whilst, tt does not provide any routing capability,
 * this class is crucial to routing user queries to appropriate actions. cf \Library\Router
 *
 * @category   Library
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
final class Uri
{

    private $request;
    protected $validator;
    protected $encryptor;

    /**
     * Constructor for the URI Library Object
     *
     * @return void
     */
    public function __construct(Request $request, Encrypt $encryptor, Validate $validator)
    {


        $this->encryptor = $encryptor;
        $this->validator = $validator;
        $this->request = $request;
        //$this->config = $container->config;

    }

    public function getHost()
    {
        return $this->request->getHost();
    }

    /**
     * Resolves a url adds path if missing
     *
     * @param string $url THe Url to internalize
     * @return string A well formed internalized URL
     */
    public function internalize($url = '')
    {

        //Are we dealing with an array of parts?
        if (is_array($url)) {
            $url = implode('/', $url);
        }

        //@TODO make sure that this url does not have the scheme
        if (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url))
            return $url;
        //@TODO make sure that this url does not have the host already
        //@TODO make sure we are internalizing a path and nothing else
        //die;
        //Do we have the path info included?
        $sPath = $this->request->getBasePath() . "/";


        if (!empty($url) && $sPath <> "/") {

            $parts = explode("/", $sPath);
            $segments = explode("/", $url);

            //Remove all empty elements
            $segments = array_filter($segments, 'strlen');
            $parts = array_filter($parts, 'strlen');
            //die;
            //This is in case we have a system deep in multiple supdirectories
            array_unshift($parts, null);
            $fragment = implode("/", $parts);

            if (is_array($segments)) {

                array_unshift($segments, null); //Adds the / to the start of the url
                $url = implode("/", $segments);

                //now look for $fragment at the start of $url
                $pos = strpos($url, $fragment);
                if ($pos !== 0 || $pos === FALSE) {
                    $url = $fragment . $url;
                }
            }
        }
        return $url;
    }

    /**
     * Adds the schema, host and path to an internal url 'path'
     *
     * @param type $path
     */
    public function externalize($path, $schema = "http")
    {

        if (!is_array($path)):
            //If already has a schema, return
            if (preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $path))
                return $path;
        endif;

        $sHost = $this->request->getHost();
        $path = $schema . "://" . $sHost . static::internalize($path);

        return $path;
    }

}