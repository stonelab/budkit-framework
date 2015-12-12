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
use DOMElement;
use DOMNode;
use DOMXPath;

class Input extends Element
{

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "input";

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
        if (!($Node instanceof DOMElement) || $Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== $this->localName) {
            return;
        }

        //parse attributes
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
        }


        //Checkbox
        if (in_array($Node->getAttribute("type"), ["checkbox", "radio"])) {

            //get the value of selected;
            $value = $this->getData($Node->getAttribute("value"), $Data, $Node->getAttribute("value"));
            $check = $this->getData($Node->getAttribute("data"), $Data, $Node->getAttribute("data"));

            if ($value == $check) {
                $Node->setAttribute("checked", "checked");
            }

        }
        //Change from tpl:input to
        $this->renameNode($Node->ownerDocument, $Node, $this->localName);
    }

    function renameNode($document, $node, $elementTag)
    {
        $newNode = $document->createElement($elementTag);
        // get all attributes from old node
        $attributes = $node->attributes;
        foreach ($attributes as $attribute) {
            $name = $attribute->name;
            $value = $attribute->value;
            $newNode->setAttribute($name, $value);
        }
        // get all children from old node
        $children = $node->childNodes;

        foreach ($children as $child) {
            // clone node and add it to newNode
            $newChild = $child->cloneNode(true);
            $newNode->appendChild($newChild);
        }
        // replace the old node with the newNode
        $node->parentNode->replaceChild($newNode, $node);

    }
}