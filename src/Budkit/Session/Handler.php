<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 27/06/2014
 * Time: 03:58
 */

namespace Budkit\Session;


interface Handler
{

    public function read($splash, $session, $sessionId);

    public function write($userdata, $splash, $session, $sessionId, $expiry);

    public function delete($where, $session);

    public function update($update, $session, $sessionId);

} 