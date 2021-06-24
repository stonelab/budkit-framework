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
use Budkit\View\Layout\Element;
use Budkit\View\Layout\Loader;
use DOMNode;

class Loop extends Element
{

    protected $Element;

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "loop";

    protected $data = [];

    protected $placemarkers = [];

    protected $loopdatakey = null;

    protected $parentdata = [];

    protected $methods;

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

    public function execute(&$Element)
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
        ) {
            return;
        }

        $document = $Node->parentNode;

        $appendedChild = null;

        if ($Node->hasAttribute("repeat")) {

            $limitBy = (int)$Node->getAttribute("repeat");

            for ($l = 0; $l < $limitBy; $l++) {
                //$_node = $document->importNode($_node, true);
                if ($Node->hasChildNodes()) {

                    for ($i = 0; $i < $Node->childNodes->length; $i++) {
                        //$_node = $document->importNode($_node, true);
                        $childNode = $Node->childNodes->item($i);


                        $newnode = $this->walk($childNode->cloneNode(true), $Data);
                        //We could just insert the childnode in thedocument,
                        //but its best to walk it ourselves with a subset of the data
                        //so the loop is respected;
                        $document->insertBefore($newnode, $Node);

                        $appendedChild = & $newnode;
                    }
                }
            }
        }

        //count
        $appendedChild = null;

        if ($Node->hasAttribute("limitby")) {
            $limit = $Node->getAttribute("limitby");
            $limitBy = (int)$this->getData($limit, $Data);

            for ($l = 0; $l < $limitBy; $l++) {
                //$_node = $document->importNode($_node, true);
                if ($Node->hasChildNodes()) {

                    for ($i = 0; $i < $Node->childNodes->length; $i++) {
                        //$_node = $document->importNode($_node, true);
                        $childNode = $Node->childNodes->item($i);

                        $newnode = $this->walk($childNode->cloneNode(true), $Data);

                        //We could just insert the childnode in thedocument,
                        //but its best to walk it ourselves with a subset of the data
                        //so the loop is respected;
                        $document->insertBefore($newnode, $Node);

                        $appendedChild = & $newnode;

                        //$document->insertBefore($this->walk($childNode->cloneNode(true), $Data), $Node->nextSibling);
                    }
                }
            }
        }

        //Foreach Loop
        $appendedChild = null;

        if ($Node->hasAttribute("foreach") || $Node->hasAttribute("foreach-line")) {

            $this->loopdatakey = null;

            $path = $Node->hasAttribute("foreach")
                ? $Node->getAttribute("foreach")
                : ( $Node->hasAttribute("foreach-line")
                    ? $Node->getAttribute("foreach-line")
                    : null  );



            $array = $this->getData($path, $Data);

            //get each line in the data input;
            if($Node->hasAttribute("foreach-line") && is_string($array)){

                //here we are exploding each line into an array;
                $array =  explode(PHP_EOL,  $array);

            }

            if (!is_array($array)) {
                $document = $Node->parentNode;
                if ($Node->nextSibling instanceof DOMNode) {
                    $Element->setResult($Node->nextSibling);
                }
                $document->removeChild($Node);

                return;
            }

            //parent data;

            //$Element->set("parentdata", $this->parentdata);

            foreach ($array as $key => $_array) {

                $this->loopdatakey  = $key;
                $this->parentdata   = $Data;

                if ($Node->hasChildNodes()) {

                    for ($i = 0; $i < $Node->childNodes->length; $i++) {
                        //$_node = $document->importNode($_node, true);
                        $childNode = $Node->childNodes->item($i);


                        //print_R($_array);

                        $newnode = $this->walk($childNode->cloneNode(true), $_array);


                        //We could just insert the childnode in thedocument,
                        //but its best to walk it ourselves with a subset of the data
                        //so the loop is respected;
                        $document->insertBefore($newnode, $Node);

                        $appendedChild = & $newnode;
                    }
                }
            }

        }

        if ($Node->nextSibling instanceof DOMNode) {
            $Element->setResult($Node->nextSibling);
        }

        $document->removeChild($Node);
    }


    /**
     * @param \DOMNode $tpl
     * @param array $data
     */
    protected function walk(DOMNode $Node, $data = [])
    {

        $parseNode = new Event('Layout.onCompile', $this, $data);

        $parseNode->set("loopdatakey", $this->loopdatakey);
        $parseNode->set("parentdata", $this->parentdata);

        //print_r($this->parentdata);

        $parseNode->setResult($Node);

        $this->observer->trigger($parseNode); //Parse the Node;

        $_Node = $parseNode->getResult();


        if ($_Node instanceof DOMNode) {

            if ($_Node->hasChildNodes()) {

                for ($i = 0; $i < $_Node->childNodes->length; $i++) {

                    $this->walk($_Node->childNodes->item($i), $data);

                }
            }
        }

        return $_Node;
    }
}