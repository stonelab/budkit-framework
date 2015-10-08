<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 20/09/15
 * Time: 14:48
 */

namespace Budkit\View\Layout\Tpl;

use Budkit\Event\Observer;
use Budkit\View\Layout\Loader;
use DOMNode;

class Condition{

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "condition";

    protected $data  = [];

    protected $placemarkers = [];


    protected $methods;


    const SEPARATOR = '/[:\.]/';

    public function __construct(Loader $loader, Observer $observer){

        $this->loader = $loader;

        $this->methods = [
            "boolean"=> "isBoolean",
            "equals"=>"isEqualTo",
            "empty"=>"isEmpty",
            "not"=>"isNot"
        ];

    }

    public function evaluate(&$Element){


        //Get the Node being Parsed;
        $Node = $Element->getResult();
        $Data  = $Element->getData();

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
        if(empty($test) || !array_key_exists($test, $this->methods)){

            $Node->parentNode->removeChild( $Node );

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
        $subject  = $this->getData( $Node->getAttribute("on"), $Data);

        //If there is no subject, return;
        if(empty($subject)){

            if ($Node->nextSibling  instanceof DOMNode ) {

                $Element->setResult($Node->nextSibling);

            }

            $Node->parentNode->removeChild( $Node );


            return;
        }

        if(!$this->$method($subject, $is)) {


            $document = $Node->parentNode;

            //print_R($document->removeChild($Node));

            //die;
            if ($Node->nextSibling  instanceof DOMNode ) {

                $Element->setResult($Node->nextSibling);

            }

            $document->removeChild( $Node );

            $Element->setResult($document);

            return;
        }

//        if($Node->hasChildNodes()){
//
//            for ($i=0; $i < $Node->childNodes->length; $i++) {
//
//                $import = $Node->childNodes->item($i);
//
//                $Node->parentNode->insertBefore( $import , $Node  );
//            }
//        }
//        $Node->parentNode->removeChild( $Node );

        $document = $Node->parentNode;

        if ($Node->hasChildNodes()) {
            foreach ($Node->childNodes as $_node) {
                //$_node = $document->importNode($_node, true);
                $document->appendChild( $_node->cloneNode(true) );
            }
        }

        if ($Node->nextSibling  instanceof DOMNode ) {

            $Element->setResult($Node->nextSibling);

        }

        $document->removeChild($Node);
        $Element->setResult($document);

    }


    protected function isEqualTo($subject, $is){


        if ($subject == $is ){
            return true;
        }

        return false;

    }

    protected function isBoolean($subject, $is){

        //this function will look wiered, but essentially
        //we are checking a boolean data type against either
        //true or false and returning accordingly

        $true = [1, "true", true];
        $false= [0, "" , "false", false, null];

        if ($subject && in_array($is, $true)){
            return true;
        }
        else if( !($subject) && in_array($is, $false)){
            return true;
        }
        return false;
    }

    //essentially the reverse of equalsTo
    protected function isNot($subject, $is){

        if($this->isEqualTo($subject, $is)){
            return false;
        }
        return true;
    }


    protected function isEmpty($subject, $is){

        //same as boolean but checking for is empty
        $true = [1, "true", true];
        $false= [0, "" , "false", false, null];

        if (empty($subject) && in_array($is, $true)){
            return true;
        }
        else if( !empty($subject) && in_array($is, $false)){
            return true;
        }
        return false;

    }


    protected function getData($path, array $data){

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