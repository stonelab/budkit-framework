<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 06/12/15
 * Time: 18:59
 */

namespace Budkit\View\Layout;


use Budkit\Event\Event;

abstract class Element{


    protected $Element;


    protected function getData($path, $data)
    {

        //when the path is just $, the element is request the data array or string as is.
        if($path == "$"){
            return $data;
        }

        //modifies such as ${config://} to get config data or do anything else fancy
        if (preg_match('|^(.*?)://(.+)$|', $path, $matches)) {

            $parseDataScheme = new Event('Layout.onCompile.scheme.data', $this, ["scheme" => $matches[1], "path" => $matches[2], "data"=>$data]);
            $parseDataScheme->setResult(null); //set initial result

            $observer = $this->getObserver();
            $observer->trigger($parseDataScheme); //Parse the Node;

            return $parseDataScheme->getResult();
        }

        $array = $data;
        $keys = $this->explode($path);


        //to get parent data add a $ as the first key, e.g "$.name"
        //reset($keys);

        if(in_array( current($keys), ["$" , "$$"] ) ){

            //echo "why three times? <br />";
            $Element = $this->getElement();

            if(!is_a($Element, Event::class)){
                return null; //must be type of event
            }

            $attributes = $Element->attributes;

           // print_r($keys); die;
            if(!is_array($attributes) || !array_key_exists("parentdata", $attributes)){
                return null;
            }

//            if($keys == "$$"){
//
//                print_R($attributes);
//
//               // die;
//            }

            //print_R($Element->attributes);
            //replace the current with parent data;
            $array = $attributes["parentdata"];

            //remove keys from key
             array_shift($keys);

        }


        //From this point we can only work with data arrays;

        if( is_array($array) || $array instanceof \ArrayAcces ) {

            foreach ($keys as $key) {
                if (isset($array[$key])) {
                    if($array instanceof \ArrayAcces){
                        $array = $array->offsetGet( $key );
                        //print_R($array);
                    }else {
                        $array = $array[$key];
                    }
                } else {
                    return "";
                }
            }

            return $array;
        }

        return null;
    }

    protected function explode($path)
    {
        return preg_split('/[:\.]/', $path);
    }


    abstract public function getObserver();
    abstract public function getElement();

}