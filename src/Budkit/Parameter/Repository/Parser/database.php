<?php

namespace Budkit\Parameter\Repository\Parser;
use Budkit\Parameter\Repository\Handler;


/**
 * What is the purpose of this class, in one sentence?
 *
 * How does this class achieve the desired purpose?
 *
 * @category   Library
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/config
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 */
final class Database implements Handler {

    public function getParams($filepath = ""){}
    public function saveParams(array $namespaces, $filepath=""){}
    public function readParams($filepath){}

}