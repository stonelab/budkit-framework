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
class Compiler implements Parser, Listener
{

    protected $masterName;
    protected $xPath;
    protected $loader;
    public $removeQueue = [];

    /**
     * @param \Budkit\View\Layout\Loader $loader
     * @param \Budkit\Event\Observer $observer
     */
    public function __construct(Loader $loader, Observer $observer)
    {

        $this->loader = $loader;
        $this->observer = $observer;

        $this->observer->attach($this, 'Layout.onCompile', $this->xPath);
    }

    /**
     * @return array
     */
    public function definition()
    {
        //Attach initial attribute handlers.
        $this->observer->attach([new Tpl\Data($this->loader, $this->observer), 'attribute'], "Layout.onCompile.attribute");

        //Return Element handlers
        return ['Layout.onCompile' => [

            //block
            //This should be LAST!
            [new Tpl\Attributes($this->loader, $this->observer), 'nodelist'],

            //foreach
            //while do
            //if then else elseif

            [new Tpl\Import($this->loader, $this->observer), 'element'],
            [new Tpl\Layout($this->loader, $this->observer), 'element'],//also implements extension;

            [new Tpl\Menu($this->loader, $this->observer), 'execute'],
            [new Tpl\Condition($this->loader, $this->observer), 'evaluate'],


            [new Tpl\Datetime($this->loader, $this->observer), 'content'],
            [new Tpl\Datepicker($this->loader, $this->observer), 'display'],
            //translate ,
            //sprintf
            //content only on Text Nodes;
            [new Tpl\Block($this->loader, $this->observer), 'position'],
            [new Tpl\Data($this->loader, $this->observer), 'content'],
            [new Tpl\Link($this->loader, $this->observer, $this->removeQueue), 'rel'],
            //processing instruction? xslt?

            //attributes Maybe run this last?
            [new Tpl\Input($this->loader, $this->observer), 'execute'],
            [new Tpl\Select($this->loader, $this->observer), 'execute'],

            [new Tpl\Loop($this->loader, $this->observer), 'execute']


        ]
        ];
    }

    /**
     * @param       $content
     * @param array $data
     *
     * @return string
     */
    public function execute($content, $data = [])
    {

        //return $content;
        $this->loader->addData($data);

        $tpl = new DOMDocument();

        $tpl->resolveExternals = true;
        $tpl->preserveWhiteSpace = false;

        //libxml_use_internal_errors(true);
        $tpl->loadXML($content, LIBXML_COMPACT & LIBXML_NOBLANKS & LIBXML_DTDATTR);
        //$this->masterName = $tpl->documentElement->attributes->getNamedItem("name")->nodeValue;
        $this->xPath = new DOMXPath($tpl);

        $this->walk($tpl, $data);

        //It is not ideal to removeNodes from the Remove Queue whilst we walk over it, therefore,
        //Some nodes might need to be removed after iteration;
        return "<!DOCTYPE html>\n".trim($tpl->saveHTML());
    }

    /**
     * @param \DOMNode $tpl
     * @param array $data
     */
    private function walk(DOMNode &$tpl, $data = [])
    {

        if ($tpl->hasChildNodes()) {

            $parseNode = new Event('Layout.onCompile', $this, $data);

            for ($i = 0; $i < $tpl->childNodes->length; $i++) {

                $Node = $tpl->childNodes->item($i);

                $parseNode->setResult($Node);

                $this->observer->trigger( $parseNode ); //Parse the Node;

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