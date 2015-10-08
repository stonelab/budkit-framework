<?php

namespace Budkit\View\Layout\Tpl;


use Budkit\View\Layout\Loader;
use DOMNode;

class Href {

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "href";


    public function __construct(Loader $loader) {
        $this->loader = $loader;
    }

    public function attribute($Element) {

        //Get the Node being Parsed;
        $Attr = $Element->getResult();

        //If we cannot determine what Node this is then stop propagation;
        if (!($Attr instanceof DOMNode)) {
            $Element->stop(); //Stop propagating this event;
            return true;
        }


        //If the node is not of type tpl:layout; return
        if ($Attr->namespaceURI !== $this->nsURI || strtolower($Attr->localName) !== $this->localName) return;

        //If can't handle attribute, leave as is; if modified stop propagation;
        $value = $Attr->value;


        $Attr->ownerElement->setAttribute("href", $value);
        $Attr->ownerElement->removeAttributeNode($Attr);

    }
}