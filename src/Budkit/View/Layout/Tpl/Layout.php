<?php
namespace Budkit\View\Layout\Tpl;

use DOMNode;
use DOMXPath;
use DOMDocument;
use Budkit\View\Layout\Loader;
use Budkit\Event\Listener;
use Budkit\Event\Observer;
use Budkit\Event\Event;

class Layout implements Listener {

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "layout";
    protected $loader;
    protected $observer;
    protected $xPath;
    protected $extending;


    public function __construct(Loader $loader, Observer $observer) {
        $this->loader = $loader;
        $this->observer = $observer;
    }

    public function definition() {
        return ['Layout.onCompile.layout.extension' => [[$this, 'prepend'], [$this, 'append'], [$this, 'replace'], [$this, 'remove']]];
    }

    public function element($Element, DOMXPath $xPath) {

        //Get the Node being Parsed;
        $Node = $Element->getResult();

        //var_dump($Node, "<br/></br/>\n\n\n");
        //If we cannot determine what Node this is then stop propagation;
        if (!($Node instanceof DOMNode)) {
            $Element->stop(); //Stop propagating this event;
            return true;
        }

        //If the node is not of type tpl:layout; return
        if ($Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== $this->localName)
            return;

        //Extending?
        $this->extension($Node, $xPath);
        $document = $Node->parentNode;

        if ($Node->hasChildNodes()) {
            foreach ($Node->childNodes as $_node) {
                //$_node = $document->importNode($_node, true);
                $document->appendChild($_node->cloneNode(true));
            }
        }

        $Node->parentNode->removeChild($Node);
        $Element->setResult($document);
    }

    private function extension(&$Node, $xPath) {
        //check node has extension attribute;
        if (!($Node instanceof DOMNode) || !$Node->hasAttribute("extends"))
            return;

        $layout = $Node->getAttribute("extends");
        $this->extending = new DOMDocument();
        $this->xPath = $xPath;

        //Get the imported document;
        $this->observer->attach($this, 'Layout.onCompile.layout.extension', $this->extending);
        $this->extending->loadXML($this->loader->find($layout), LIBXML_COMPACT);

        if ($Node->hasChildNodes()) {

            $parseExtension = new Event('Layout.onCompile.layout.extension', $this);

            foreach ($Node->childNodes as $_node) {

                $parseExtension->setResult($_node);
                $this->observer->trigger($parseExtension); //Parse the Node;
                //$_node = $document->importNode($_node, true);
                //$document->appendChild($_node->cloneNode(true));
            }
        }

        //import this extending; to the current document; and replace this Node;
        $import = $this->xPath->document->importNode($this->extending->documentElement, true);
        $xPath->document->replaceChild($import, $Node);

        $Node = $import;
    }

    public function append($Extension, &$Extending) {

        //Get the Node being Parsed;
        $Node = $Extension->getResult();

        if (!($Node instanceof DOMNode)) {
            $Extension->stop(); //Stop propagating this event;
            return;
        }
        if ($Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== "append" || !$Node->hasAttribute("path"))
            return;

        //Find Nodes to append;
        $Xpath = new DOMXPath($Extending);
        $Xpath->registerNamespace("tpl", $this->nsURI);

        $blocks = $Xpath->query($Node->getAttribute("path"));

        if ($blocks->length > 0) {
            foreach ($blocks as $to) {
                foreach ($Node->childNodes as $import) {
                    $import = $Extending->importNode($import, true);
                    $to->appendChild($import);
                }
            }
        }
    }

    public function remove($Extension, &$Extending) {

        //Get the Node being Parsed;
        $Node = $Extension->getResult();

        if (!($Node instanceof DOMNode)) {
            $Extension->stop(); //Stop propagating this event;
            return;
        }
        if ($Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== "remove" || !$Node->hasAttribute("path"))
            return;

        //Find Nodes to append;
        $Xpath = new DOMXPath($Extending);
        $Xpath->registerNamespace("tpl", $this->nsURI);

        $blocks = $Xpath->query($Node->getAttribute("path"));

        if ($blocks->length > 0) {
            foreach ($blocks as $remove) {
                $remove->parentNode->removeChild($remove);
            }
        }
    }

    /**
     * @param $Extension
     * @param $Extending
     */
    public function replace($Extension, &$Extending) {
        return $this->prepend($Extension, $Extending, true);
    }

    public function prepend($Extension, &$Extending, $replace = false) {

        //Get the Node being Parsed;
        $Node = $Extension->getResult();

        if (!($Node instanceof DOMNode)) {
            $Extension->stop(); //Stop propagating this event;
            return;
        }
        if ($Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== (($replace) ? "replace" : "prepend") || !$Node->hasAttribute("path"))
            return;

        //Find Nodes to append;
        $Xpath = new DOMXPath($Extending);
        $Xpath->registerNamespace("tpl", $this->nsURI);

        $blocks = $Xpath->query($Node->getAttribute("path"));

        if ($blocks->length > 0) {
            foreach ($blocks as $replace) {
                //$before = $replace;
                foreach ($Node->childNodes as $import) {
                    $import = $Extending->importNode($import, true);
                    $replace->parentNode->insertBefore($import, $replace);
                }
                if ($replace)
                    $replace->parentNode->removeChild($replace);
            }
        }
    }
}