<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 20/09/15
 * Time: 14:48
 */

namespace Budkit\View\Layout\Tpl;

use Budkit\Event\Event;
use Budkit\Event\Observer;
use Budkit\Helper\Time;
use Budkit\View\Layout\Element;
use Budkit\View\Layout\Loader;
use DOMNode;

class Datetime extends Element
{

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "datetime";

    protected $data = [];

    protected $placemarkers = [];


    public function __construct(Loader $loader, Observer $observer)
    {

        $this->loader = $loader;
        $this->observer = $observer;

    }

    public function getObserver(){
        return $this->observer;
    }

    public function getElement(){
        return $this->Element;
    }

    public function content(&$Element)
    {

        $this->Element = $Element;

        //Get the Node being Parsed;
        $Node = $Element->getResult();
        $Data = $Element->getData();

        //var_dump($Node, "<br/></br/>\n\n\n");
        //If we cannot determine what Node this is then stop propagation;
        if (!($Node instanceof DOMNode)) {
            $Element->stop(); //Stop propagating this event;
            return true;
        }

        //If the node is not of type tpl:layout; return
        if ($Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== $this->localName
            || !$Node->hasAttribute("value")
        ) {
            return;
        }

        $dataPath = $Node->getAttribute("value");
        $dataType = $Node->getAttribute("format");

        $replace = $this->getData($dataPath, $Data);

        if (!empty($dataType)) {
            $custom = ["diff"];
            if (in_array($dataType, $custom)) {

                $replace = Time::difference(strtotime($replace));
            } else {
                $replace = date($dataType, strtotime($replace));
            }
        }

        if (is_string($replace)) {
            $text = $Node->ownerDocument->createTextNode(trim($replace));
            $Node->parentNode->replaceChild($text, $Node);
        }
    }
}
