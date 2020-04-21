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
use Budkit\View\Layout\Element;
use Budkit\View\Layout\Loader;
use DOMNode;

class Condition extends Element
{

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "condition";

    protected $data = [];

    protected $placemarkers = [];


    protected $methods;

    public function __construct(Loader $loader, Observer $observer)
    {

        $this->loader = $loader;
        $this->observer = $observer;

        $this->methods = [
            "boolean" => "isBoolean",
            "equals" => "isEqualTo",
            "empty" => "isEmpty",
            "not" => "isNot",
            "isset" => "isDefined"
        ];

        $this->observer->attach([$this, "evaluateAttribute"], "Layout.onCompile.scheme.data");

    }


    public function getObserver(){
        return $this->observer;
    }

    public function getElement(){
        return $this->Element;
    }


    public function evaluateAttribute(&$event)
    {
        $parent = $this->getElement();
        $attributes = $parent->attributes;


        $this->Element = $event;

        if(is_array($attributes) && array_key_exists("parentdata", $attributes)){
            $this->Element->set($attributes, null); //completely replace the attributes
        }
        //Because this is an attribute, we need to cascade the parent of the element to the attribute;


        //print_r($event);

        $scheme = $event->getData("scheme");
        $data = $event->getData("data");

        //if://required!=true

        //The premise of this method is that if the attribute value is null,
        //then it wont pe set on the parent node
        //so this scheme checks if the data meets a certain criteria
        //and only returns it if true, or returns null otherwise.

        if (strtolower($scheme) == "if") {

            $path = $event->getData("path");

            $operators = ["="=>"isEqualTo","!="=>"isNot"];
            $segments = preg_split("/(=|!=)/", $path, 2, PREG_SPLIT_DELIM_CAPTURE);

            $replace = $this->getData($segments[0], $data);

            //check checking if the var isset
            if(count($segments) < 2 && isset($segments[0])){

                return $event->setResult( $replace );

            }else{

                if(isset($segments[1]) && isset($segments[2]) && isset($operators[$segments[1]])){

                    $method = $operators[$segments[1]];
                    $_value = $this->getData($segments[2], $data);
                    $value  = !empty($_value) ? $_value : $segments[2];


                    if ($this->$method($replace, $value)) {
                        return $event->setResult($replace);
                    }
                }
            }

            return null;
            //if the scheme is config://get.config.path, then load the config data;
            //return $event->setResult(trim($this->application->config->get($path)));
        }

    }


    public function evaluate(&$Element)
    {


        $this->Element = $Element;

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
            || !$Node->hasAttribute("is") || !$Node->hasAttribute("test") || !$Node->hasAttribute("on")
        ) {
            return;
        }


        $test = $Node->getAttribute("test");

        //If there is no test, return
        if (empty($test) || !array_key_exists($test, $this->methods)) {

            $Node->parentNode->removeChild($Node);

            return;
        }

        //die;

        $is = $Node->getAttribute("is");


        //Search for (?<=\$\{)([a-zA-Z]+)(?=\}) and replace with data
        if (preg_match_all('/(?:(?<=\$\{)).*?(?=\})/i', $is, $matches)) {

            $placemarkers = (is_array($matches) && isset($matches[0])) ? $matches[0] : array();
            $searches = [];
            $replaces = [];

            //die;

            foreach ($placemarkers as $placemarker):

                $replace = $this->getData($placemarker, $Data);

                if (is_string($replace)) {
                    $searches[] = '${' . $placemarker . '}';
                    $replaces[] = $replace;
                }

            endforeach;

            //perform replace
            $is = str_ireplace($searches, $replaces, $is);
        }

        $method = $this->methods[$test];
        $subject = $this->getData($Node->getAttribute("on"), $Data);

        //echo $is; die;

        //If there is no subject, return;
//        if(empty($subject)){
//
//            if ($Node->nextSibling  instanceof DOMNode ) {
//
//                $Element->setResult($Node->nextSibling);
//
//            }
//
//            $Node->parentNode->removeChild( $Node );
//
//
//            return;
//        }


        if (!$this->$method($subject, $is)) {

            $document = $Node->parentNode;
            $document->removeChild($Node);

            if ($Node->nextSibling instanceof DOMNode) {
                $Element->setResult($Node->nextSibling);
            }

            return;
        }

        $document = $Node->parentNode;

        if ($Node->hasChildNodes()) {

            for ($i = 0; $i < $Node->childNodes->length; $i++) {

                $import = $Node->childNodes->item($i);

                //$_node = $document->importNode($import, true);
                $document->insertBefore($import->cloneNode(true), $Node);

                if ($test == "not") {
                    //print_R($import);
                }
            }
        }
//        $Node->parentNode->removeChild( $Node );


//        if ($Node->hasChildNodes()) {
//            foreach ($Node->childNodes as $_node) {
//                //$_node = $document->importNode($_node, true);
//                $document->appendChild( $_node->cloneNode(true) );
//            }
//        }

        if ($Node->nextSibling instanceof DOMNode) {

            $Element->setResult($Node->nextSibling);

        }

        $document->removeChild($Node);
        $Element->setResult($document);

    }


    protected function isEqualTo($subject, $is)
    {


        if ($subject == $is) {
            return true;
        }

        return false;

    }


    protected function isBoolean($subject, $is)
    {

        //this function will look wiered, but essentially
        //we are checking a boolean data type against either
        //true or false and returning accordingly
        $true = [1, "true"];
        //$false= [0, "", "false", false, null];

        $testIs = (in_array($is, $true)) ? true : false;

        if ((bool)$subject === (bool)$testIs) {
            return true;
        }


        return false;
    }

    //essentially the reverse of equalsTo
    protected function isNot($subject, $is)
    {

        if ($this->isEqualTo($subject, $is)) {

            return false;
        }

        return true;
    }


    protected function isEmpty($subject, $is)
    {

        //same as boolean but checking for is empty
        $true = [1, "true", true, "1"];
        $false = [0, "", "false", false, null, "0"];


        if (  empty($subject) && in_array($is, $true)) {
            //echo $subject;

            return true;
        } else if (!empty($subject) && in_array($is, $false)) {

            //print_r($subject);
            return true;
        }

        return false;

    }
}