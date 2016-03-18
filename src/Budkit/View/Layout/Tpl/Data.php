<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 20/09/15
 * Time: 14:48
 */

namespace Budkit\View\Layout\Tpl;

use Budkit\View\Layout\Element;
use Budkit\Event\Observer;
use Budkit\View\Layout\Loader;
use Budkit\View\Layout\Utility\Markdown;
use DOMNode;

class Data extends Element
{
    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "data";

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

    public function content($Element)
    {

        $this->Element = $Element;

        //Get the Node being Parsed;
        $Node   = $Element->getResult();
        $Data   = $Element->getData();

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
        $replace = $this->getData($dataPath, $Data);

        if(empty($replace) && $Node->hasAttribute("default")){
            $replace = $Node->getAttribute("default");
        }

        if (is_string($replace)) {

            if($Node->hasAttribute("parsedown")){

                //echo $replace;

                $replace = Markdown::instance()
                        // ->setBreaksEnabled(true) # enables automatic line breaks
                        ->text( $replace );
            }


            //lets import the markup;
            if ($Node->hasAttribute("markup") && !empty($replace)) {

                $tmpImport = $Node->ownerDocument->createElement("div");
                $tmpDoc = new \DOMDocument();

                libxml_use_internal_errors(TRUE);
                $tmpDoc->loadHTML($replace, LIBXML_COMPACT | LIBXML_HTML_NOIMPLIED);
                libxml_clear_errors();

                if ($tmpDoc->documentElement->hasChildNodes()) {

                    for ($i = 0; $i < $tmpDoc->documentElement->childNodes->length; $i++) {

                        $import = $tmpDoc->documentElement->childNodes->item($i);

                        $_Node = $Node->ownerDocument->importNode($import, true);
                        $tmpImport->appendChild($_Node->cloneNode(true));

                    }
                }

                $Node->parentNode->replaceChild($tmpImport, $Node);

            } else {

                $text = $Node->ownerDocument->createTextNode(trim($replace));
                $Node->parentNode->replaceChild($text, $Node);
            }
        }
    }

    public function attribute($Element)
    {

        $this->Element = $Element;

        //Get the Node being Parsed;
        $Attr = $Element->getResult();
        $Data = $Element->getData("data");

        //If we cannot determine what Node this is then stop propagation;
        if (!($Attr instanceof DOMNode)) {
            $Element->stop(); //Stop propagating this event;
            return true;
        }

        //If the node is not of type tpl:layout; return
        if ($Attr->namespaceURI !== $this->nsURI || strtolower($Attr->prefix) !== "tpl") return;

        //Search for (?<=\$\{)([a-zA-Z]+)(?=\}) and replace with data
        if (preg_match_all('/(?:(?<=\$\{)).*?(?=\})/i', $Attr->value, $matches)) {

            $placemarkers = (is_array($matches) && isset($matches[0])) ? $matches[0] : array();
            $searches = [];
            $replaces = [];

            foreach ($placemarkers as $placemarker):

                $replace = $this->getData($placemarker, $Data);

                if (is_string($replace)) {
                    $searches[] = '${' . $placemarker . '}';
                    $replaces[] = $replace;
                }

            endforeach;

            //perform replace
            //if(!empty($replace))
            $value = str_ireplace($searches, $replaces, $Attr->value);

            if($value != $Attr->value) {

                $Attr->ownerElement->setAttribute($Attr->localName, $value);
                $Element->setResult($Attr);
            }
            $Attr->ownerElement->removeAttributeNode($Attr);

        }
    }
}