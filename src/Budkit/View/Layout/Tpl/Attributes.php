<?php

    namespace Budkit\View\Layout\Tpl;


    use \DOMNode;
    use \DOMXPath;
    use \DOMDocument;
    use Budkit\View\Layout\Loader;
    use Budkit\Event\Listener;
    use Budkit\Event\Observer;
    use Budkit\Event\Event;

    class Attributes implements Listener {

        protected $nsURI = "http://budkit.org/tpl";

        protected $xPath;

        public function __construct(Loader $loader, Observer $observer) {

            $this->loader = $loader;
            $this->observer = $observer;

            $this->observer->attach($this, 'Layout.onCompile.attribute', $this->xPath);
        }

        public function definition() {
            return array('Layout.onCompile.attribute' => array(

                //translate ,
                //sprintf

                array(new Href($this->loader), 'attr')));
               //content only on Text attributes; run last because removes namespace;
        }

        public function nodelist($Element, DOMXPath $xPath) {

            //Get the Node being Parsed;
            $Node = $Element->getResult();

            //var_dump($Node, "<br/></br/>\n\n\n");
            //If we cannot determine what Node this is then stop propagation;
            if (!($Node instanceof DOMNode)) {
                $Element->stop(); //Stop propagating this event;
                return true;
            }

            if ($Node->hasAttributes()) {

                $Attributes = $xPath->query("@*[namespace-uri()='{$this->nsURI}']", $Node);
                $parseAttribute = new Event('Layout.onCompile.attribute', $this, $Element->getData());

                foreach ($Attributes as $attribute) {

                    $parseAttribute->setResult($attribute);

                    //Callbacks on each Node;
                    $this->observer->trigger($parseAttribute); //Parse the Node;

                    if ($parseAttribute->getResult() instanceof DOMNode) {
                        $attribute = $parseAttribute->getResult();
                    }
                }
            }
        }
    }