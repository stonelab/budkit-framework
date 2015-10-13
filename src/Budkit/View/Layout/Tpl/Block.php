<?php

namespace Budkit\View\Layout\Tpl;

use Budkit\Event\Event;
use Budkit\Event\Observer;
use Budkit\View\Layout\Loader;
use DOMNode;

class Block
{

    protected $nsURI = "http://budkit.org/tpl";
    protected $localName = "block";
    protected $loader;
    protected $observer;
    protected $removeQueue = array();


    const SEPARATOR = '/[:\.]/';


    public function __construct(Loader $loader, Observer $observer, Array &$removeQueue = array())
    {

        $this->loader = $loader;
        $this->observer = $observer;
        $this->removeQueue = &$removeQueue;

        //Attach the listener
        $this->observer->attach($this);

    }

    public function position($Element)
    {


        //Get the Node being Parsed;
        $Node = $Element->getResult();
        $Data = $Element->getData();

        //set string content of block at position main;
        //$this->setContent("string content", "main");

        //set layout content of block at position main;
        //$this->setContent("import://admin/console/widgets", "main");

        //var_dump($Node, "<br/></br/>\n\n\n");
        //If we cannot determine what Node this is then stop propagation;
        if (!($Node instanceof DOMNode)) {
            $Element->stop(); //Stop propagating this event;
            return true;
        }

        //If the node is not of type tpl:layout; return
        if ($Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== $this->localName
            || !$Node->hasAttribute("position")
        ) {
            return;
        }

        $position = $Node->getAttribute("position");
        $blocks = $this->getData("block." . $position, $Data);


        $document = $Node->parentNode;

        if (is_array($blocks) && !empty($blocks)) {

            foreach ($blocks as $insert) {

                if (preg_match('|^(import?://)(.+)$|', $insert, $matches)) {

                    if (!isset($matches[2])) continue; //we need the layout;

                    $import = $document->ownerDocument->createElementNS($this->nsURI, "tpl:import");
                    $import->setAttribute("name", $matches[2]); //tell it the layout we want to import

                    //We need to first append child here;
                    $document->insertBefore($import, $Node);

                } else {

                    if (is_string($insert)) {
                        $text = $Node->ownerDocument->createTextNode($insert);
                        $Node->parentNode->insertBefore($text, $Node);
                    }
                }
            }
        }

        if ($Node->nextSibling instanceof DOMNode) {
            $Element->setResult($Node->nextSibling);
        }

        $document->removeChild($Node);

    }

    protected function walk(DOMNode $Node, $data = [])
    {

        $parseNode = new Event('Layout.onCompile', $this, $data);

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


    protected function getData($path, array $data)
    {

        $array = $data;
        $keys = $this->explode($path);

        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $array = $array[$key];
            } else {
                return "";
            }
        }

        return $array;
    }

    protected function explode($path)
    {
        return preg_split(self::SEPARATOR, $path);
    }

}