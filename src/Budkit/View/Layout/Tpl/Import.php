<?php

    namespace Budkit\View\Layout\Tpl;


    use DOMNode;
    use DOMXPath;
    use DOMDocument;
    use Budkit\View\Layout\Loader;

    class Import {

        protected $nsURI = "http://budkit.org/tpl";

        protected $localName = "import";


        public function __construct(Loader $loader) {
            $this->loader = $loader;
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
            if ($Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== $this->localName || !$Node->hasAttribute("name"))
                return;

            //<tpl:import name="layoutname[.tpl|.php|.html|.xml]" [frompath="/path/to/layout"] />
            $viewpath = $Node->getAttribute("frompath") . $Node->getAttribute("name");
            $imported = new DOMDocument();

            //Get the imported document;
            $imported->loadXML($this->loader->find($viewpath), LIBXML_COMPACT);
            $import = $xPath->document->importNode($imported->documentElement, true);

            //fallbacks as in xinclude?
            //<tpl:import name="layoutname[.tpl|.php|.html|.xml]" [frompath="/path/to/layout"] >
            //   <tpl:fallback></tpl:fallback>
            //</tpl:import>

            //Append the layout in place of import tags;
            $Node->parentNode->appendChild($import);
            $Node->parentNode->removeChild($Node);

            $Element->setResult($import);

        }
    }