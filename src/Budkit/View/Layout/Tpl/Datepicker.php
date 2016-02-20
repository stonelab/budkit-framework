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
use Budkit\Helper\Time;
use Budkit\View\Layout\Element;
use Budkit\View\Layout\Loader;
use DOMNode;
use DOMXPath;

class Datepicker extends Element
{
    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "datepicker";

    protected $data = [];

    protected $placemarkers = [];


    public function __construct(Loader $loader, Observer $observer)
    {

        $this->loader = $loader;
        $this->observer = $observer;

    }

    public function getObserver(){
        return $this->observer;
    }

    public function getElement(){
        return $this->Element;
    }

    public function display(&$Element, DOMXPath $xPath)
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
        if ($Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== $this->localName) {
            return;
        }


        //parse attributes
        if ($Node->hasAttributes()) {

            $Attributes = $xPath->query("@*[namespace-uri()='{$this->nsURI}']", $Node);
            $parseAttribute = new Event('Layout.onCompile.attribute', $this, ["data" => $Data, "xPath" => $xPath]);

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
        //get the value of selected;
        $datepicker = $Node->ownerDocument->createElement("div");
        $datepicker->setAttribute("data-type", "datepicker");

        switch($Node->getAttribute("type")){

            //@TODO add hour and min settings
            case "min":
                $this->getMinSelect( $Node , $datepicker, $Data);
                break;
            case "hour":
                $this->getHourSelect( $Node , $datepicker, $Data);
                break;
            case "day":
                $this->getDaySelect( $Node , $datepicker, $Data);
                break;
            case "month":
                $this->getMonthSelect( $Node , $datepicker, $Data);
                break;
            case "year":
                $this->getYearSelect( $Node , $datepicker, $Data);
                break;
            default:
                $this->getDaySelect($Node , $datepicker, $Data);
                $this->getMonthSelect($Node , $datepicker, $Data);
                $this->getYearSelect($Node , $datepicker, $Data);

                $this->getHourSelect($Node , $datepicker, $Data);
                $this->getMinSelect($Node , $datepicker, $Data);
                break;

        }

        $Node->parentNode->replaceChild($datepicker, $Node);

        //5. Replace the datepicker with the node;
        $Element->setResult( $datepicker );
    }


    final private function getMinSelect(&$tag, &$datepicker, $data = []){

        $minute       = $this->getData( $tag->getAttribute("value") , $data);
        $value      = empty($minute)? $tag->getAttribute("value") : $minute ;

        $name       = $tag->hasAttribute("name") ? $tag->getAttribute("name") : "_minute";
        $disabled   = $tag->hasAttribute("disabled") ? explode(',', $tag->getAttribute("disabled") ) : [];

        $select     = $tag->ownerDocument->createElement("select");

        $select->setAttribute("name", $name);
        $select->setAttribute("data-type", "datepicker-minute");

        //some months don't have 32 days but you will need to validate this in your data acquisition
        for($i=0; $i<60; $i++):

            $option = $tag->ownerDocument->createElement("option", str_pad( strval($i), 2, "0", STR_PAD_LEFT)." min");
            $option->setAttribute("value", $i);

            if((int)$value == $i) $option->setAttribute("selected", "selected");
            if(in_array($i, $disabled)) $option->setAttribute("disabled", "disabled");

            $select->appendChild( $option );

        endfor;

        $datepicker->appendChild( $select );

        return $select;
    }

    final private function getHourSelect(&$tag, &$datepicker, $data = []){

        $hour       = $this->getData( $tag->getAttribute("value") , $data);
        $value      = empty($hour)? $tag->getAttribute("value") : $hour ;

        $name       = $tag->hasAttribute("name") ? $tag->getAttribute("name") : "_hour";
        $disabled   = $tag->hasAttribute("disabled") ? explode(',', $tag->getAttribute("disabled") ) : [];

        $select     = $tag->ownerDocument->createElement("select");

        $select->setAttribute("name", $name);
        $select->setAttribute("data-type", "datepicker-hour");

        //some months don't have 32 days but you will need to validate this in your data acquisition
        for($i=0; $i<24; $i++):
            //00 to 23 h
            $option = $tag->ownerDocument->createElement("option", str_pad( strval($i), 2, "0", STR_PAD_LEFT)." h" );
            $option->setAttribute("value", $i);

            if((int)$value == $i) $option->setAttribute("selected", "selected");
            if(in_array($i, $disabled)) $option->setAttribute("disabled", "disabled");

            $select->appendChild( $option );

        endfor;

        $datepicker->appendChild( $select );

        return $select;

    }


    final private  function getDaySelect(&$tag, &$datepicker, $data = []){

        $day       = $this->getData( $tag->getAttribute("value") , $data);
        $value      = empty($day)? $tag->getAttribute("value") : $day ;

        $name       = $tag->hasAttribute("name") ? $tag->getAttribute("name") : "_day";
        $disabled   = $tag->hasAttribute("disabled") ? explode(',', $tag->getAttribute("disabled") ) : [];

        $select     = $tag->ownerDocument->createElement("select");

        $select->setAttribute("name", $name);
        $select->setAttribute("data-type", "datepicker-day");

        //some months don't have 32 days but you will need to validate this in your data acquisition
        for($i=1; $i<32; $i++):

            $option = $tag->ownerDocument->createElement("option", $i);
            $option->setAttribute("value", $i);

            if((int)$value == $i) $option->setAttribute("selected", "selected");
            if(in_array($i, $disabled)) $option->setAttribute("disabled", "disabled");

            $select->appendChild( $option );

        endfor;

        $datepicker->appendChild( $select );

        return $select;

    }

    final private function getMonthSelect(&$tag, &$datepicker, $data = []){

        $month       = $this->getData( $tag->getAttribute("value") , $data);
        $value      = empty($month)? $tag->getAttribute("value") : $month ;

        //$value      = $tag->hasAttribute("value") ? date("n", @strtotime($tag->getAttribute("value")) ) : date("n");
        $name       = $tag->hasAttribute("name") ? $tag->getAttribute("name") : "_month";
        $disabled   = $tag->hasAttribute("disabled") ? explode(',', $tag->getAttribute("disabled") ) : [];

        $select     = $tag->ownerDocument->createElement("select");

        $select->setAttribute("name", $name);
        $select->setAttribute("data-type", "datepicker-month");

        $months    = array( t("January"),t("February"),t("March"),t("April"),t("May"),t("June"),t("July"),t("August"),t("September"),t("October"),t("November"),t("December"));

        for($i=1; $i<13; $i++):

            $option = $tag->ownerDocument->createElement("option", $months[($i-1)]);
            $option->setAttribute("value", $i);

            if((int)$value == $i) $option->setAttribute("selected", "selected");
            if(in_array($i, $disabled)) $option->setAttribute("disabled", "disabled");

            $select->appendChild( $option );

        endfor;

        $datepicker->appendChild( $select );

        return $select;
    }

    final private function getYearSelect(&$tag, &$datepicker, $data = []){


        $year       = $this->getData( $tag->getAttribute("value") , $data);
        $value      = empty($year)? $tag->getAttribute("value") : $year ;

        //$value      = $tag->hasAttribute("value") ? date("Y", @strtotime($tag->getAttribute("value")) ) : date("Y");
        $name       = $tag->hasAttribute("name") ? $tag->getAttribute("name") : "_year";
        $range       = $tag->hasAttribute("range") ? $tag->getAttribute("range") : "-10";
        $limit       = $tag->hasAttribute("limit") ? $tag->getAttribute("limit") : "+10";
        $disabled   = $tag->hasAttribute("disabled") ? explode(',', $tag->getAttribute("disabled") ) : [];

        $select     = $tag->ownerDocument->createElement("select");

        $select->setAttribute("name", $name);
        $select->setAttribute("data-type", "datepicker-year");

        $current = intval( date("Y") );
        $start   = ($current+ (int)$range);
        $end   = ($current+ (int)$limit);


        for($i=$start; $i<($end+1); $i++):

            $option = $tag->ownerDocument->createElement("option", $i);
            $option->setAttribute("value", $i);

            if((int)$value == $i) $option->setAttribute("selected", "selected");
            if(in_array($i, $disabled)) $option->setAttribute("disabled", "disabled");

            $select->appendChild( $option );

        endfor;

        $datepicker->appendChild( $select );

        return $select;
    }
}