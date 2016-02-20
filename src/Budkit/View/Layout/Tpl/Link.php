<?php

namespace Budkit\View\Layout\Tpl;


use Budkit\Event\Event;
use Budkit\Event\Listener;
use Budkit\Event\Observer;
use Budkit\View\Layout\Element;
use Budkit\View\Layout\Loader;
use DOMNode;

class Link extends Element implements Listener
{

    protected $nsURI = "http://budkit.org/tpl";
    protected $localName = "link";
    protected $loader;
    protected $observer;

    protected $xPath;

    protected $removeQueue = array();

    public function __construct(Loader $loader, Observer $observer, Array &$removeQueue = array())
    {

        $this->loader = $loader;
        $this->observer = $observer;
        $this->removeQueue = &$removeQueue;

        //Attach the listener
        $this->observer->attach($this, 'Layout.onCompile.link', $this->xPath);

    }

    public function getObserver(){
        return $this->observer;
    }

    public function getElement(){
        return $this->Element;
    }

    public function definition()
    {
        return ['Layout.onCompile.link' => 'person'];
        //content only on Text attributes; run last because removes namespace;
    }

    public function rel($Element, \DOMXPath $xPath)
    {
        $this->Element = $Element;

        //Get the Node being Parsed;
        $Node = $Element->getResult();
        $Data = $Element->getData();

        if (!($Node instanceof DOMNode)) {
            $Element->stop(); //Stop propagating this event;
            return;
        }

        //If the node is not of type tpl:layout; return
        if ($Node->namespaceURI !== $this->nsURI
            || strtolower($Node->localName) !== $this->localName
            || !$Node->hasAttribute("rel")
        ) {
            return;
        }

        //Parse the link type;
        $parseLink = new Event('Layout.onCompile.link', $this, ["rel"=>$Node->getAttribute("rel"), "data" => $Data, "xPath" => $xPath]); //pass the rel as data;
        $parseLink->setResult($Node);

        //var_dump($Node);

        $this->observer->trigger($parseLink); //Parse the Node;

        //nothing is returned;
        //$Element->stop();
    }

    public function person($node)
    {


        $this->Element = $node;

        //return;
        $rel = $node->getData("rel"); //var_dump($rel);
        $link = $node->getResult();
        $data = $node->getData("data");
        $xPath = $node->getData("xPath");

        if (strtolower($rel) !== "person") return;

        //<a class="person-link" href="/personlink">
        //<span>Person Name</span>
        //<img class="person-dp" src="" />
        //<span class="status" />
        //</a>

        //1. change the localname to <a>
        $wrapper = $link->hasAttribute("wrap") ? $link->getAttribute("wrap") : "a";
        $anchor = $link->ownerDocument->createElement($wrapper);
        $exclude = ['rel', 'src', 'status', 'width', 'height', 'wrap'];


        $attributes = $xPath->query("@*[namespace-uri()='{$this->nsURI}']", $link);
        $parseAttribute = new Event('Layout.onCompile.attribute', $this, ["data" => $data, "xPath" => $xPath]);

        //cascade parseNode or Element event attributes to parseAttribute attributes
        //so that important event details such as needed in data loops are handled;
        $parseAttribute->set("attributes", $node->get("attributes"));


        foreach ($attributes as $attribute) {

            //print_r($attribute->nodeValue);

            $parseAttribute->setResult($attribute);

            //Callbacks on each Node;
            $this->observer->trigger($parseAttribute); //Parse the Node;

            if ($parseAttribute->getResult() instanceof DOMNode) {
                $attribute = $parseAttribute->getResult();
            }

        }



        foreach ($link->attributes as $attribute){

//            if(strtolower($attribute->prefix) !== "tpl") {
//
//                $parseAttribute->setResult($attribute);
////
//                //Callbacks on each Node;
//                $this->observer->trigger($parseAttribute); //Parse the Node;
//
//                if ($parseAttribute->getResult() instanceof DOMNode) {
//                    $attribute = $parseAttribute->getResult();
//                }
//            }

            $attr = strtolower($attribute->nodeName);

            //var_dump($attribute);

            if (!in_array($attr, $exclude) ) {

                $anchor->setAttribute($attr,  $attribute->nodeValue  );
            }
        }

        //2. if has attribute src then add img src;
        if ($link->hasAttribute("src")) {
            $img = $link->ownerDocument->createElement("img");

            $img->setAttribute("src", $link->getAttribute("src"));
            $img->setAttribute("class", "person-photo");

            //Does the image have a width?
            if ($link->hasAttribute("width")) {
                $img->setAttribute("width", $link->getAttribute("width"));
            }

            //Does the image have a height;
            if ($link->hasAttribute("height")) {
                $img->setAttribute("height", $link->getAttribute("height"));
            }
            $anchor->setAttribute("class", $anchor->getAttribute('class')." has-person-photo");
            $anchor->appendChild($img);
        }

        //4. if has attribute name="" then add span to hod the persons name;
        if ($link->hasAttribute("name")) {
            $span = $link->ownerDocument->createElement("span", $link->getAttribute("name"));
            $span->setAttribute("class", "person-name");
            $anchor->setAttribute("class", $anchor->getAttribute('class')." has-person-name");
            $anchor->appendChild($span);
        }

        //3. if has attribute status="" then add span status link;
        if ($link->hasAttribute("status")) {
            $span = $link->ownerDocument->createElement("span");
            $span->setAttribute("class", "person-status " . $link->getAttribute("status"));
            $anchor->setAttribute("class", $anchor->getAttribute('class')." has-person-status");
            $anchor->appendChild($span);
        }


        $link->parentNode->replaceChild($anchor, $link);
        $this->removeQueue[] = $link;

        //5. Replace the link in the parent document with the $anchor;
        $node->setResult($anchor);
    }
}