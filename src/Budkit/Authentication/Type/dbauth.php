<?php

namespace Budkit\Authentication\Type;

use Budkit\Authentication\Handler;
use Budkit\Datastore\Encrypt;
use Budkit\Datastore\Model\Entity;
use Budkit\Validation\Validate;
use Exception;


/**
 * What is the purpose of this class, in one sentence?
 *
 * How does this class achieve the desired purpose?
 *
 * @category   Utility
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
class DbAuth implements Handler
{

    protected $entity;

    protected $encryptor;

    protected $validator;


    public function __construct(Entity $entity, Encrypt $encryptor, Validate $validator)
    {
        $this->entity = $entity;
        $this->encryptor = $encryptor;
        $this->validator = $validator;

    }


    public function attest(array $credentials)
    {

        //If not credentials
        if (empty($credentials) || !array_key_exists("usernameid", $credentials) || !array_key_exists("usernamepass", $credentials)) {
            throw new Exception(t('Must specify a valid usernameid and password'));
            return false;
        }

        //We don't want empty passwords or usernames;
        if (empty($credentials['usernamepass']) || empty($credentials['usernamepass'])) {
            throw new Exception(t('Must specify a valid usernameid and password'));
            return false;
        }

        //If usernameid an email 
        $usernameid = $credentials['usernameid'];
        $objects = $this->entity;

        $objects->defineValueGroup("user"); //Means we are getting the data from the users value proxy table;

        //$object     = $objects->getObjectsByPropertyValueMatch( array("user_email"), array( $usernameid ) , array("user_password", "user_name_id", "user_email","user_first_name","user_last_name","user_middle_name"));

        if ($this->validator->isEmail($credentials['usernameid'])) {
            //treat as user_email, 
            $statement = $objects->getObjectsByPropertyValueMatch(array("user_email"), array($usernameid), array("user_password", "user_name_id", "user_email", "user_first_name", "user_last_name", "user_middle_name")); //Use EAV to get data;
        } else {
            //use as user_name_id
            $statement = $objects->getObjectsByPropertyValueMatch(array("user_name_id"), array($usernameid), array("user_password", "user_name_id", "user_email", "user_first_name", "user_last_name", "user_middle_name")); //Use EAV to get the data
        }

        $result = $statement->execute();


        //If we did not find any user with this id or password;
        if ((int)$result->getAffectedRows() < 1) {
            return false;
        }

        //Get the user object;
        $userobject = $result->fetchObject();
        $passparts = explode(":", $userobject->user_password);
        $passhash = $this->encryptor->hash($credentials['usernamepass'], $passparts[1]);

        //Are the passhashes similar?
        if ($passhash !== $userobject->user_password) {
            return false;
        }

        return $userobject;
    }
}