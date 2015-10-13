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
use Budkit\View\Layout\Loader;
use DOMElement;
use DOMNode;

class Input
{

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "input";

    protected $data = [];

    protected $methods;


    const SEPARATOR = '/[:\.]/';

    public function __construct(Loader $loader, Observer $observer)
    {

        $this->loader = $loader;
        $this->observer = $observer;

    }

    public function execute(&$Element)
    {


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


    protected function getData($path, array $data, $default = "")
    {
        if (preg_match('|^(.*)://(.+)$|', $path, $matches)) {

            $parseDataScheme = new Event('Layout.onCompile.scheme.data', $this, ["scheme" => $matches[1], "path" => $matches[2]]);

            //$parseDataScheme->setResult(null); //set initial result

            $this->observer->trigger($parseDataScheme); //Parse the Node;

            return $parseDataScheme->getResult();
        }

        $array = $data;
        $keys = $this->explode($path);

        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $array = $array[$key];
            } else {
                return $default;
            }
        }

        return $array;
    }

    protected function explode($path)
    {
        return preg_split(self::SEPARATOR, $path);
    }


}