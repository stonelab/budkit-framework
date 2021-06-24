<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Budkit\Session\Repository;

use Budkit\Datastore\Database as Datastore;
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
final class Database implements Handler
{


    private $database;


    public function __construct(Datastore $database)
    {

        $this->database = $database;
    }

    /**
     * Read the session from the database
     *
     * @param type $splash
     * @param type $session
     * @param type $sessionId
     * @return boolean
     */
    public function read($splash, $session, $sessionId)
    {

        $database = $this->database;
        //$token = (string) $input->getCookie($sessId);
        $statement =
            $database->where("session_agent", $database->quote($splash['agent']))
                ->where("session_ip", $database->quote($splash['ip']))
                ->where("session_host", $database->quote($splash['domain']))
                ->where("session_key", $database->quote($sessionId))
                ->select("*")->from($session->table)->prepare();

        $result = $statement->execute();

        //Do we have a session that fits this criteria in the db? if not destroy
        if ((int)$result->rowCount() < 1) {
            $session->destroy($sessionId);
            return false; //will lead to re-creation
        }

        $object = $result->fetchObject();

        return $object;
    }

    /**
     * Updates session data in the database;
     *
     * @param type $userdata
     * @param type $session
     * @param type $sessionId
     */
    public function update($update, $session, $sessionId)
    {

        $database = $this->database;
        if (isset($update["session_registry"])) {
            $update["session_registry"] = $database->quote($update["session_registry"]);
        }

        //now update the session;
        $database->update($session->table, $update, array("session_key" => $database->quote($sessionId)));

        return true;
    }

    /**
     * Deletes session data from the database
     *
     * @param type $where
     * @param type $session
     * @return boolean
     */
    public function delete($where, $session)
    {

        $database = $this->database;

        if (isset($where["session_key"])) {
            $where["session_key"] = $database->quote($where["session_key"]);
        }
        $database->delete($session->table, $where);

        return true;
    }

    /**
     * Writes session data to the database store
     *
     * @param type $userdata
     * @param type $splash
     * @param type $session
     * @param type $sessionId
     * @param type $expiry
     */
    public function write($userdata, $splash, $session, $sessionId, $expiry)
    {

        $database = $this->database;

        $database->insert($session->table, array(
            "session_key" => $database->quote($sessionId),
            "session_ip" => $database->quote($splash['ip']),
            "session_host" => $database->quote($splash['domain']),
            "session_agent" => $database->quote($splash['agent']),
            "session_token" => $database->quote($splash['token']),
            "session_expires" => $expiry,
            "session_lastactive" => time(),
            "session_registry" => $database->quote($userdata)
        ));
    }

}

