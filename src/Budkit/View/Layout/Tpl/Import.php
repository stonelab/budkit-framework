<?php

namespace Budkit\View\Layout\Tpl;


use Budkit\Event\Observer;
use Budkit\View\Layout\Element;
use Budkit\View\Layout\Loader;
use DOMDocument;
use DOMNode;
use DOMXPath;

class Import extends Element
{

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "import";

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

    public function element(&$Element, DOMXPath $xPath)
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
            || (!$Node->hasAttribute("name") && !$Node->hasAttributeNS($this->nsURI, "name") )
        ) {
            return;
        }

        //<tpl:import name="layoutname[.tpl|.php|.html|.xml]" [frompath="/path/to/layout"] />

        $viewpath = $Node->getAttribute("frompath").(($Node->hasAttributeNS($this->nsURI, "name") )
                ? $this->getName( $Node->getAttributeNS($this->nsURI, "name") , $Data)
                : $Node->getAttribute("name"));
        $imported = new DOMDocument();

//        if(($Node->hasAttributeNS($this->nsURI, "name") )) {
//            //echo $Node->getAttribute("frompath").$this->getName( $Node->getAttributeNS($this->nsURI, "name") , $Data);
//            echo "$viewpath";
//
//            print_r( $this->loader->find($viewpath) );
//
//            die;
//        }

        //Get the imported document;
        $imported->loadXML($this->loader->find($viewpath),  LIBXML_COMPACT | LIBXML_NOBLANKS | LIBXML_DTDATTR | LIBXML_HTML_NOIMPLIED);
        $import = $xPath->document->importNode($imported->documentElement, true);

        //@TODO fallbacks as in xinclude?
        //<tpl:import name="layoutname[.tpl|.php|.html|.xml]" [frompath="/path/to/layout"] >
        //   <tpl:fallback></tpl:fallback>
        //</tpl:import>


        //Append the layout in place of import tags;
        $Node->parentNode->appendChild($import);
        $Node->parentNode->replaceChild($import, $Node);

        $Element->setResult($import);

    }

    protected function getName($name, $Data){

        //Search for (?<=\$\{)([a-zA-Z]+)(?=\}) and replace with data
        if (preg_match_all('/(?:(?<=\$\{)).*?(?=\})/i', $name, $matches)) {

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
            $value = str_replace($searches, $replaces, $name);


            //print_R($value); die;

            return $value;

        }else{

            return $this->getData($name, $Data);;
        }
    }
}