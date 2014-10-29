<?php

namespace Budkit\View\Layout;

use Budkit\Event\Event;
use Budkit\Event\Listener;
use Budkit\Event\Observer;
use DOMDocument;
use DOMNode;
use DOMXPath;

/**
 * Class Compiler
 *
 * @package Budkit\View\Layout
 */
class Compiler implements Parser, Listener {

    protected $masterName;
    protected $xPath;
    protected $loader;
	public    $removeQueue = [];

    /**
     * @param \Budkit\View\Layout\Loader $loader
     * @param \Budkit\Event\Observer     $observer
     */
    public function __construct(Loader $loader, Observer $observer) {

        $this->loader   = $loader;
        $this->observer = $observer;

        $this->observer->attach($this, 'Layout.onCompile', $this->xPath);
    }

    /**
     * @return array
     */
    public function definition() {

        return ['Layout.onCompile' => [

            //block

            //foreach
            //while do
            //if then else elseif

            [new Tpl\Import($this->loader), 'element'],
            [new Tpl\Layout($this->loader, $this->observer), 'element'],//also implements extension;

            //translate ,
            //sprintf
            //content only on Text Nodes;
            [new Tpl\Content($this->loader, $this->observer), 'text'],
            [new Tpl\Link($this->loader, $this->observer, $this->removeQueue), 'rel'],
            //processing instruction? xslt?

            //attributes Maybe run this last?
            [new Tpl\Attributes($this->loader, $this->observer), 'nodelist']
            ]
        ];
    }

    /**
     * @param       $content
     * @param array $data
     *
     * @return string
     */
    public function execute($content, $data = []) {

        //return $content;

        $tpl = new DOMDocument();

        $tpl->resolveExternals = true;
        $tpl->preserveWhiteSpace = false;

        $tpl->loadXML($content, LIBXML_COMPACT & LIBXML_NOBLANKS);
        //$this->masterName = $tpl->documentElement->attributes->getNamedItem("name")->nodeValue;
        $this->xPath = new DOMXPath($tpl);

        $this->walk($tpl, $data);
		
		//It is not ideal to removeNodes from the Remove Queue whilst we walk over it, therefore,
		//Some nodes might need to be removed after iteration;
        return $tpl->saveHTML();
    }

    /**
     * @param \DOMNode $tpl
     * @param array    $data
     */
    private function walk(DOMNode &$tpl, $data = []) {

        if ($tpl->hasChildNodes()) {
			
			$parseNode = new Event('Layout.onCompile', $this, $data);

            foreach ($tpl->childNodes as $Node) {

                $parseNode->setResult($Node);
                $this->observer->trigger($parseNode); //Parse the Node;

				
                if ($parseNode->getResult() instanceof DOMNode) {
                    $_Node = $parseNode->getResult();
	                if ($_Node->hasChildNodes()) {
	                    $this->walk($_Node, $data);
	                }
                }
            }
        }
    }
}