<?php

namespace Budkit\View\Layout\Tpl;


use Budkit\Event\Event;
use Budkit\Event\Listener;
use Budkit\Event\Observer;
use Budkit\View\Layout\Loader;
use DOMNode;
use DOMXPath;

class link implements Listener {

    protected $nsURI = "http://budkit.org/tpl";
    protected $localName = "link";
    protected $loader;
    protected $observer;
	protected $removeQueue = array();

    public function __construct(Loader $loader, Observer $observer, Array &$removeQueue = array()) {

        $this->loader   	= $loader;
        $this->observer 	= $observer;
		$this->removeQueue 	= &$removeQueue;
		
		//Attach the listener
		$this->observer->attach($this);

    }

    public function definition() {
        return ['Layout.onCompile.link' => 'person' ];
        //content only on Text attributes; run last because removes namespace;
    }

    public function rel($Element) {

        //Get the Node being Parsed;
        $Node = $Element->getResult();

        if (!($Node instanceof DOMNode)) {
            $Element->stop(); //Stop propagating this event;
            return;
        }

        //If the node is not of type tpl:layout; return
        if ($Node->namespaceURI !== $this->nsURI
            || strtolower($Node->localName) !== $this->localName
            || !$Node->hasAttribute("rel")) {
            return;
        }
		
        //Parse the link type;
        $parseLink = new Event('Layout.onCompile.link', $this, $Node->getAttribute("rel")); //pass the rel as data;
        $parseLink->setResult($Node);
		
		//var_dump($Node);
		
        $this->observer->trigger($parseLink); //Parse the Node;

		//nothing is returned;
		//$Element->stop();
    }

    public function person( $node ){
		
		//return; 
        $rel 	= $node->getData(); //var_dump($rel);
		$link 	= $node->getResult();
		 
        if(strtolower( $rel) !== "person") return;
		
		//<a class="person-link" href="/personlink">
			//<span>Person Name</span>
			//<img class="person-dp" src="" />
			//<span class="status" />
		//</a>
		
		//1. change the localname to <a>
		$wrapper    = $link->hasAttribute("wrap")? $link->getAttribute("wrap") : "a";
		$anchor 	= $link->ownerDocument->createElement( $wrapper );
		$exclude 	= ['rel','src', 'status', 'width', 'height' , 'wrap' ]; 
		foreach($link->attributes as $attribute){
			$attr = strtolower($attribute->nodeName);
			if(!in_array($attr, $exclude)){
				$anchor->setAttribute($attr, $attribute->nodeValue);
			}
		}
		
		//2. if has attribute src then add img src;
		if($link->hasAttribute("src")){
			$img 	= $link->ownerDocument->createElement("img");
			
			$img->setAttribute("class", "person-photo");
			$img->setAttribute("src", $link->getAttribute("src"));
			
			//Does the image have a width?
			if($link->hasAttribute("width")){
				$img->setAttribute("width", $link->getAttribute("width"));
			}
			
			//Does the image have a height;
			if($link->hasAttribute("height")){
				$img->setAttribute("height", $link->getAttribute("height"));
			}
			$anchor->appendChild( $img );
		}
		
		//3. if has attribute status="" then add span status link;
		if($link->hasAttribute("status")){
			$span 	= $link->ownerDocument->createElement("span");
			$span->setAttribute("class", "person-status ".$link->getAttribute("status"));
			$anchor->appendChild( $span );
		}
		
		//4. if has attribute name="" then add span to hod the persons name;
		if($link->hasAttribute("name")){
			$span 	= $link->ownerDocument->createElement("span", $link->getAttribute("name"));
			$span->setAttribute("class", "person-name");
			$anchor->appendChild( $span );
		}
		
		$link->parentNode->replaceChild( $anchor, $link );
		$this->removeQueue[] = $link ;
				
		//5. Replace the link in the parent document with the $anchor;
		$node->setResult( $anchor );
    }
}