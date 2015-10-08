<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 20/09/15
 * Time: 14:48
 */

namespace Budkit\View\Layout\Tpl;

use Budkit\Event\Observer;
use Budkit\Event\Event;
use Budkit\View\Layout\Loader;
use DOMNode;
use DOMElement;
use DOMXPath;

class Select
{

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "select";

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
        if ($Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== $this->localName ||
        !$Node->hasAttribute("selected")) {
            return;
        }

        $document = $Node->parentNode;


        $xpath = new DOMXPath($Node->ownerDocument);
        $select = $Node->ownerDocument->createElement("select");
        $options = $xpath->query('.//option', $Node); //the dot at the start is importat to make it relative to the context node


        if($Node->hasAttributes()){
            foreach($Node->attributes as $attribute){
                if(strtolower($attribute->nodeName) !== "selected"){
                    $select->setAttribute($attribute->nodeName, $attribute->nodeValue);
                }
            }
        }


        //get the value of selected;
        $_select  = $Node->getAttribute("selected");
        $selected = $this->getData($_select, $Data, $_select);


        if($options->length){
            for ($i = 0; $i < $options->length; $i++) {
                //$_node = $document->importNode($_node, true);
                $childNode = $options->item($i);

                // check and select options value;
                if(is_a($childNode, DOMNode::class)){

                    //print_R($childNode);

                    if($childNode->hasAttributes()){


                        if($childNode->hasAttribute("value")){


                            if($childNode->getAttribute("value") == $selected ){


                                $childNode->setAttribute("selected", "true");
                            }
                        }
                    }
                }

                $select->appendChild( $childNode );
            }
        }

        //Replace this select;
        $document->replaceChild($select, $Node);
    }



    protected function getData($path, array $data, $default = "")
    {
        if (preg_match('|^(.*)://(.+)$|', $path, $matches)) {

            $parseDataScheme = new Event('Layout.onCompile.scheme.data', $this, ["scheme" => $matches[1], "path"=>$matches[2] ]);

            $parseDataScheme->setResult(null); //set initial result

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