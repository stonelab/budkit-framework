<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * sinleton.php
 *
 * Requires PHP version 5.4
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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/i18n
 * @since      Class available since Release 1.0.0 June 6, 2012 9:09:41 AM
 *
 */

namespace Budkit\Session\Repository;

use Budkit\Parameter\Repository\Parser\Ini;
use Budkit\Session\Handler;

/**
 * What is the purpose of this class, in one sentence?
 *
 * How does this class achieve the desired purpose?
 *
 * @category   Libraries
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/i18n
 * @since      Class available since Release 1.0.0 Jan 15, 2012 3:09:41 AM
 */
class File implements Handler
{


    /**
     * Reads session data from file
     *
     * @param type $splash
     * @param type $session
     * @param type $sessionId
     */
    public function read($splash, $session, $sessionId)
    {

        $ini = new Ini();
        $store = $session->folder . DIRECTORY_SEPARATOR;

        //We need to know the array;
        if (!is_array($splash)) return false;

        $sfile = $store . $sessionId . ".ini";


        if (!$ini->isFile($sfile)) {
            //If we can't file the sessionf file;
            //throw new \Exception("The session file does not exists");
            return false;
        }

        //Temporarily chmode the file;
        $ini->chmod($sfile, 0755);

        if (!$ini->readParams($sfile)) {
            throw new \Exception("Could not read the session configuration file");
            return false;
        }

        $sess = $ini->getParams($sfile);

        if (!is_array($sess) || empty($sess)) {
            throw new \Exception("Invalid session file");
            return false;
        }

        //return data as an object;
        $object = new \stdClass;
        $object->session_id = $sessionId;
        $object->session_key = $sess['key'];
        $object->session_ip = $sess['ip'];
        $object->session_host = $sess['domain'];
        $object->session_agent = $sess['agent'];
        $object->session_token = $sess['token'];
        $object->session_expires = $sess['expiry'];
        $object->session_lastactive = $sess['active'];
        $object->session_registry = $sess['registry'];

        return $object;

    }

    /**
     * Updates session data in file store
     *
     * @param type $update
     * @param type $session
     * @param type $sessionId
     */
    public function update($update, $session, $sessionId)
    {
    }

    /**
     * Delete session data from file store
     *
     * @param type $where
     * @param type $session
     */
    public function delete($where, $session)
    {
    }

    /**
     * Writes session data to file store
     *
     * @param type $userdata
     * @param type $splash
     * @param type $session
     * @param type $sessionId
     * @param type $expiry
     */
    public function write($userdata, $splash, $session, $sessionId, $expiry)
    {

        $ini = new Ini();
        $store = $session->folder . DIRECTORY_SEPARATOR;

        if (!$ini->is($store)) {
            if (!$ini->create($store)) {
                throw new \Exception("Could not create the $store folder. Please create this manually and check it hass writable permissions");
                return false;
            }
        }

        //We need to know the array;
        if (!is_array($splash)) return false;

        $sfile = $store . $sessionId . ".ini";


        //We create the session file;
        if (!($sini = $ini->create($sfile))) {
            throw new \Exception("Could not create the session file");
            return false;
        }
        //Temporarily chmode the file;
        $ini->chmod($sfile, 0755);

        $splash['expiry'] = $expiry;
        $splash['active'] = time();
        $splash['key'] = $sessionId;
        $splash['registry'] = static::quote("'" . $userdata . "'");


        //Now write to file
        if (!$ini->write($sfile, $ini->toIniString($splash))) {
            throw new \Exception("Could not write out to the configuration file");
            return false;
        }

        return true;
    }

    /**
     * Quotes strings for storage in the ini file
     *
     * @param type $string
     * @return type
     */
    private static function quote($string)
    {
        return $string;
    }

}

