<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * defines.php
 *
 * Requires PHP version 5.3
 *
 * LICENSE: This source file is subject to version 3.01 of the GNU/GPL License
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.txt  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 * send a note to support@stonyhillshq.com so we can mail you a copy immediately.
 *
 * @category   Config
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/utilities/
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 */

define('DS', DIRECTORY_SEPARATOR);

/**
 * Define Codes
 */
define('HTTP_CONTINUE', 100);
define('HTTP_SPROTOCOL', 101);
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_ACCEPTED', 202);
define('HTTP_NAI', 203);
define('HTTP_NO_CONTENT', 204);
define('HTTP_RESET', 206);
define('HTTP_PARTIAL_CONTENT', 206);
define('HTTP_PREDIRECT', 301);
define('HTTP_FOUND', 302);
define('HTTP_SEE_OTHER', 303);
define('HTTP_NOT_MODIFIED', 304);
define('HTTP_USE_PROXY', 305);
define('HTTP_TREDIRECT', 307);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORISED', 401);
define('HTTP_FEE_REQUIRED', 402);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_NOT_ALLOWED', 405);
define('HTTP_NOT_ACCEPTABLE', 406);
define('HTTP_AUTH_REQUIRED', 407);
define('HTTP_REQUEST_TIMEOUT', 408);
define('HTTP_CONFLICT', 409);
define('HTTP_GONE', 410);
define('HTTP_LENGTH_REQUIRED', 411);
define('HTTP_PRE_FAIL', 412);
define('HTTP_LARGE_REQUEST_E', 413);
define('HTTP_LARGE_REQUEST_U', 414);
define('HTTP_NO_MEDIA_TYPE', 415);
define('HTTP_BAD_REQ_RANGE', 416);
define('HTTP_EXPECT_FAIL', 417);
define('HTTP_SERVER_ERROR', 500);
define('HTTP_NOT_IMPLEMENTED', 501);
define('HTTP_BAD_GATEWAY', 502);
define('HTTP_UNAVAILABLE', 503);
define('HTTP_GATEWAY_TIMEOUT', 504);


//AUTHORITY IMPLIED PERMISSIONS;
//Anonymous < Authenticated < Moderator < Curator < MasterAdmin
define('AUTHROITY_IMPLIED_ANONYMOUS', 193); //Unknown authority
//Authenticated. Has NO permission
define('AUTHROITY_IMPLIED_AUTHENTICATED', 226); //Authenticated auhtority
//Master authority
//The SUPER_ADMINISTRATOR authority curator. Has super admin permissions 111 to that authority and every subauthority
//You can have multiple master admin, but the first uer created at install is the main authority master admin. 
//There must be at least 1 curator for this authority
define('AUTHROITY_IMPLIED_MASTERADMIN', 517);

//A sub authority curator  who is not the SUPER_ADMINISTRATOR authority curator is a moderator of that authority
//A moderator is an authority administrator
//There must be at least 1 curator for this authority
define('AUTHORITY_IMPLIED_MODERATOR', 314);


define('APPLICATION_ERROR', 9033);
define('PLATFORM_ERROR', 9032);
define('LIBRARY_ERROR', 9031);