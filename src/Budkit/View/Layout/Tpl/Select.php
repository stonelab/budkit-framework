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
use DOMXPath;

class Select extends Element
{

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "select";

    protected $data = [];

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

    public function execute(&$Element, DOMXPath $xPath)
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
        if ($Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== $this->localName ||
            !$Node->hasAttribute("selected")
        ) {
            return;
        }

        $document = $Node->parentNode;


        $xpath = new DOMXPath($Node->ownerDocument);
        $select = $Node->ownerDocument->createElement("select");

        $options = $xpath->query('.//option', $Node); //the dot at the start is important to make it relative to the context node


        if ($Node->hasAttributes()) {

            $Attributes = $xPath->query("@*[namespace-uri()='{$this->nsURI}']", $Node);
            $parseAttribute = new Event('Layout.onCompile.attribute', $this, ["data" => $Data, "xPath" => $xPath]);

            //cascade parseNode or Element event attributes to parseAttribute attributes
            //so that important event details such as needed in data loops are handled;
            $parseAttribute->set("attributes", $Element->get("attributes"));

            foreach ($Attributes as $attribute) {

                $parseAttribute->setResult($attribute);

                //Callbacks on each Node;
                $this->observer->trigger($parseAttribute); //Parse the Node;

                if ($parseAttribute->getResult() instanceof DOMNode) {
                    $attribute = $parseAttribute->getResult();
                }
            }

            foreach ($Node->attributes as $attribute) {

                if (strtolower($attribute->nodeName) !== "selected") {

                    $select->setAttribute($attribute->nodeName, $attribute->nodeValue);

                }
            }

        }


        //get the value of selected;
        $_select = $Node->getAttribute("selected");
        $selected = $this->getData($_select, $Data, $_select);


        if ($options->length) {
            for ($i = 0; $i < $options->length; $i++) {
                //$_node = $document->importNode($_node, true);
                $childNode = $options->item($i);

                // check and select options value;
                if (is_a($childNode, DOMNode::class)) {

                    //print_R($childNode);

                    if ($childNode->hasAttributes()) {


                        if ($childNode->hasAttribute("value")) {


                            if ($childNode->getAttribute("value") == $selected) {


                                $childNode->setAttribute("selected", "true");
                            }
                        }
                    }
                }

                $select->appendChild($childNode);
            }
        }

        //Replace this select;
        $document->replaceChild($select, $Node);
    }
}