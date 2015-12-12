<?php

namespace Budkit\View\Layout\Tpl;


use Budkit\Event\Event;
use Budkit\Event\Listener;
use Budkit\Event\Observer;
use Budkit\View\Layout\Loader;
use DOMNode;
use DOMXPath;

class Attributes implements Listener
{

    protected $nsURI = "http://budkit.org/tpl";

    protected $xPath;

    protected $data;

    public function __construct(Loader $loader, Observer $observer)
    {

        $this->loader = $loader;
        $this->observer = $observer;
        $this->observer->attach($this, 'Layout.onCompile.attribute', $this->xPath);
    }

    public function definition()
    {

        return [
            'Layout.onCompile.attribute' => [

                //[new Data($this->loader, $this->observer), 'attribute'],
                //translate ,
                //sprintf

                //Mark item as selected="true" if value="" attribute matches tpl:selected=""
                //[new Href($this->loader), 'attribute']
                //[new Select($this->loader, $this->observer), 'attribute']
            ]
        ];
        //content only on Text attributes; run last because removes namespace;
    }


    public function nodelist($Element, DOMXPath $xPath)
    {

        //Get the Node being Parsed;
        $Node = $Element->getResult();
        $data = $Element->getData();

        //var_dump($Node, "<br/></br/>\n\n\n");
        //If we cannot determine what Node this is then stop propagation;
        if (!($Node instanceof DOMNode)) {
            $Element->stop(); //Stop propagating this event;
            return true;
        }

        if ($Node->hasAttributes()) {


            $Attributes = $xPath->query("@*[namespace-uri()='{$this->nsURI}']", $Node);
            $parseAttribute = new Event('Layout.onCompile.attribute', $this, ["data" => $data, "xPath" => $xPath]);

            //cascade parseNode or Element event attributes to parseAttribute attributes
            //so that important event details such as needed in data loops are handled;
            $parseAttribute->set("attributes", $Element->get("attributes"));

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