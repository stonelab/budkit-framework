<?php

    namespace Budkit\View\Layout\Tpl;

    use DOMNode;
    use DOMXPath;
    use DOMDocument;
    use Budkit\View\Layout\Loader;
    use Budkit\Event\Listener;
    use Budkit\Event\Observer;
    use Budkit\Event\Event;

    /**
     * Class Content
     *
     * @package Budkit\View\Layout\Tpl
     */
    class Content implements Listener {

        /**
         * @var string
         */
        protected $nsURI = "http://budkit.org/tpl";

        /**
         * @var
         */
        protected $xPath;

        /**
         * @param \Budkit\View\Layout\Loader $loader
         * @param \Budkit\Event\Observer     $observer
         *
         */
        public function __construct(Loader $loader, Observer $observer) {
            $this->loader = $loader;
            $this->observer = $observer;

            $this->observer->attach($this, 'Layout.onCompile.content', $this->xPath);
        }

        /**
         * @return array
         */
        public function definition() {
            return array('Layout.onCompile.content' => array(
                //translator?
            ));
        }

        /**
         * @param           $Element
         * @param \DOMXPath $xPath
         *
         * @return bool
         */
        public function text($Element, DOMXPath $xPath) {
            $Node = $Element->getResult();
            if (!($Node instanceof DOMNode)
                || $Node->nodeType !== XML_TEXT_NODE
                    ||$Node->isWhitespaceInElementContent()) {
                return;
            }

            //pregmatc {}, {{}} and {{{@var}}} and parse to output;
            //

            //var_dump($Node->wholeText);
        }
    }