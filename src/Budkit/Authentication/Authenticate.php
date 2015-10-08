<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * authenticate.php
 *
 * Requires PHP version 5.3
 *
 * LICENSE: This source file is subject to version 3.01 of the GNU/GPL License 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.txt  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 * send a note to support@stonyhillshq.com so we can mail you a copy immediately.
 *
 * @category   Utility
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/authenticate
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 * 
 */

namespace Budkit\Authentication;

use Budkit\Authentication\User;
use Budkit\Datastore\Model\Entity;
use Budkit\Helper\Object;
use Budkit\Authentication\Handler;
use Budkit\Session\Store as Session;


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
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/authenticate
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
class Authenticate extends Object{

    /**
     * 
     * @var type 
     */
    protected $userid = 0;

    /**
     * The type of authentication that was successful
     *
     * @var type string
     * @access public
     */
    var $type = '';

    /**
     * Any UTF-8 string that the End User wants to use as a username.
     *
     * @var fullname string
     * @access public
     */
    var $username = '';

    /**
     * Any UTF-8 string that the End User wants to use as a password.
     *
     * @var password string
     * @access public
     */
    var $password = '';

    /**
     * The email address of the End User as specified in section 3.4.1 of [RFC2822]
     *
     * @var email string
     * @access public
     */
    var $email = '';

    /**
     * UTF-8 string free text representation of the End User's full name.
     *
     * @var fullname string
     * @access public
     */
    var $fullname = '';

    /**
     * End User's preferred language as specified by ISO639.
     *
     * @var fullname string
     * @access public
     */
    var $language = '';

    /**
     * ASCII string from TimeZone database
     *
     * @var fullname string
     * @access public
     */
    var $timezone = '';

    /**
     * Holds a boolean value whether the user is authenticated or not
     *
     * @var authenticated boolean
     * @access public
     */
    var $authenticated = FALSE;

    /**
     * Constructor
     *
     * @param string $name The type of the response
     * @since 1.0.0
     * @return void
     */
    public function __construct( $splash = []) {
        foreach ($splash as $property => $value) {
            $this->$property = $value;
        }
    }

    public function execute(array $credentials, User $user, Handler $handler){

        if ( ($authUserObject = $handler->attest($credentials) ) !== false ) {

            //Destroy this session
            //$session->gc($session->getId());

            $this->authenticated = true;
            $this->type = 'dbauth';
            $this->user_id = $authUserObject->object_id;
            $this->user_name_id = $authUserObject->user_name_id;
            $this->user_email = $authUserObject->user_email;
            $this->user_first_name = $authUserObject->user_first_name;
            $this->user_last_name = $authUserObject->user_last_name;
            $this->user_full_name    = implode(' ', array($authUserObject->user_first_name, $authUserObject->user_middle_name, $authUserObject->user_last_name) );

            $session = $user->getSession();

            if(!is_a($session, Session::class)){
                throw new \Exception("Session returned by {User::class} must be an instance of {Session::class}");
                return false;
            }

            //Update
            $session->set("handler", $this, "auth");
            $session->lock("auth");
            $session->update( $session->getId() );

            return true;
        }

        return false;

    }

}