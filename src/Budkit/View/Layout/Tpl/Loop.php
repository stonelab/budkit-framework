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
use DOMNode;

class Loop
{

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "loop";

    protected $data = [];

    protected $placemarkers = [];


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
        if ($Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== $this->localName
        ) {
            return;
        }

        $document = $Node->parentNode;

        if ($Node->hasAttribute("repeat")) {

            $limitBy = (int)$Node->getAttribute("repeat");

            for ($l = 0; $l < $limitBy; $l++) {
                //$_node = $document->importNode($_node, true);
                if ($Node->hasChildNodes()) {

                    for ($i = 0; $i < $Node->childNodes->length; $i++) {
                        //$_node = $document->importNode($_node, true);
                        $childNode = $Node->childNodes->item($i);

                        //We could just insert the childnode in thedocument,
                        //but its best to walk it ourselves with a subset of the data
                        //so the loop is respected;
                        $document->insertBefore($this->walk($childNode->cloneNode(true), $Data), $Node);
                    }
                }
            }
        }

        //count
        if ($Node->hasAttribute("limitby")) {
            $limit = $Node->getAttribute("limitby");
            $limitBy = (int)$this->getData($limit, $Data);

            for ($l = 0; $l < $limitBy; $l++) {
                //$_node = $document->importNode($_node, true);
                if ($Node->hasChildNodes()) {

                    for ($i = 0; $i < $Node->childNodes->length; $i++) {
                        //$_node = $document->importNode($_node, true);
                        $childNode = $Node->childNodes->item($i);

                        //We could just insert the childnode in thedocument,
                        //but its best to walk it ourselves with a subset of the data
                        //so the loop is respected;
                        $document->insertBefore($this->walk($childNode->cloneNode(true), $Data), $Node);
                    }
                }
            }
        }

        //Foreach Loop
        if ($Node->hasAttribute("foreach")) {

            $path = $Node->getAttribute("foreach");
            $array = $this->getData($path, $Data);

            if (!is_array($array)) {
                $document = $Node->parentNode;
                if ($Node->nextSibling instanceof DOMNode) {
                    $Element->setResult($Node->nextSibling);
                }
                $document->removeChild($Node);

                return;
            }


            foreach ($array as $_array) {

                if ($Node->hasChildNodes()) {

                    for ($i = 0; $i < $Node->childNodes->length; $i++) {
                        //$_node = $document->importNode($_node, true);
                        $childNode = $Node->childNodes->item($i);

                        //We could just insert the childnode in thedocument,
                        //but its best to walk it ourselves with a subset of the data
                        //so the loop is respected;
                        $document->insertBefore($this->walk($childNode->cloneNode(true), $_array), $Node);
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