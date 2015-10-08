<?php

namespace Budkit\Authentication\Type;

use Budkit\Authentication\Handler;


/**
 * What is the purpose of this class, in one sentence?
 *
 * How does this class achieve the desired purpose?
 *
 * @category   Platform
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/authenticate/openid
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
class Openid implements Handler{
    
    /**
     * Validates the user login credentials
     * 
     * @param type $credentials 
     */
    public function attest(array $credentials){}

}