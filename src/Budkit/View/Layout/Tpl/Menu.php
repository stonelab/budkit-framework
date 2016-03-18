<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 23/09/15
 * Time: 22:57
 */

namespace Budkit\View\Layout\Tpl;

use Budkit\Event\Event;
use Budkit\Event\Observer;
use Budkit\View\Layout\Element;
use Budkit\View\Layout\Loader;
use DOMNode;

class Menu extends Element
{

    protected $nsURI = "http://budkit.org/tpl";

    protected $localName = "menu";

    protected $data = [];


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

    public function execute(&$Element)
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
        if ($Node->namespaceURI !== $this->nsURI || strtolower($Node->localName) !== $this->localName || !$Node->hasAttribute("uid")) {
            return;
        }


        //echo $Node->getAttribute("uid");

        $document = $Node->parentNode;
        //print_R($document->removeChild($Node));

        $menuItemsLoadEvent = new Event('Layout.beforeCompile.menu.data', $this, ["uid" => $Node->getAttribute("uid")]);

        //Callbacks on each Node;
        $this->observer->trigger($menuItemsLoadEvent); //Parse the Node;

        $menuItems = $menuItemsLoadEvent->getResult();


        //Use this event to extend the loaded menu.
        //beforeCompile.menu.data should not really be used.
        //Will need to think of private events
        $menuItemsExtendEvent = new Event('Layout.onCompile.menu.data', $this, ["uid" => $Node->getAttribute("uid")]);
        $menuItemsExtendEvent->setResult($menuItems);

        $this->observer->trigger($menuItemsExtendEvent); //Parse the Node;

        $menuItems = $menuItemsExtendEvent->getResult();


        if (!empty($menuItems) && is_array($menuItems)) {


            //Process the menu;
            $menuDepth = $Node->hasAttribute("depth") ? (int)trim($Node->getAttribute("depth")) : 3;
            $menuIcons = $Node->hasAttribute("show-icons") ? true : false;
            $menuType = $Node->hasAttribute("type") ? trim(strtolower($Node->getAttribute("type"))) : "nav";
            $menuClasses = $Node->hasAttribute("class") ? trim($Node->getAttribute("class")) : "";

            $list = $this->render($Node, $menuItems, $menuType, $menuDepth, $menuIcons);

            $list->setAttribute("class", $menuClasses . ' ' . $list->getAttribute("class"));

            //set the new menu list;
            $document->replaceChild($list, $Node);


        } else {

            //Else just remove the item;
            if ($Node->nextSibling instanceof DOMNode) {
                $Element->setResult($Node->nextSibling);
            }
            $document->removeChild($Node);
        }
    }

    /**
     * Create element
     *
     * @param type $menuItems
     * @return type
     */
    public function render(DOMNode $Node, $menuItems, $menuType = "nav", $menuDepth = 3, $menuIcons = true, array $parents = [])
    {

        $list = $Node->ownerDocument->createElement("ul");
        $depth = 1;

        //$hasActive  = false;
        foreach ($menuItems as $item) {

            //if this user does not have access to this menu ignore it
            if (!empty($item['menu_url'])):
                //check has permission;
                $menuItemRenderEvent = new Event('Layout.beforeRender.menu.item', $this, ["item" => $item]);

                //Callbacks on each Node;
                $this->observer->trigger($menuItemRenderEvent); //Parse the Node;


                $item = $menuItemRenderEvent->getResult();


                if (!isset($item['menu_viewable']) || !$item['menu_viewable']) {
                    continue;
                }

            endif;

            //@TODO Menu Plugins
            //Search for all plugin placemarkers in menu item names
            //Search for (?<=\$\{)([a-zA-Z]+)(?=\}) and replace with data
//            if (preg_match_all('/(?:(?<=\%\{)).*?(?=\})/i', $item['menu_title'], $matches)) {
//                $placemarkers = (is_array($matches) && isset($matches[0])) ? $matches[0] : array();
//                foreach ($placemarkers as $k => $dataid) {
//                    //@TODO Now call all menu items plugins
//                    $item['menu_title'] = $dataid;
//                }
//                //Replace with data;
//                continue;
//            }

            //@TODO check if this is the current menu item and set it as active
            //Checked in the helper and rendered at $item["menu_isactive"];
            $active = (isset($item["menu_isactive"]) && (bool)$item["menu_isactive"]) ? true : false;
            $class = str_replace(array(" ", "(", ")", "-", "&", "%", ",", "#"), '-', strtolower($item['menu_title']));

            //Add the active class to the parent;
            if ($active && !empty($parents)) {
                foreach ($parents as $parent) {
                    if (is_a($parent, DOMNode::class)) {
                        if ($parent->hasAttribute("class")) {
                            if (preg_match("/\bactive\b/i", $parent->getAttribute("class"))) {
                                continue;
                            }
                            $parent->setAttribute("class", "active " . $parent->getAttribute("class"));
                        }
                        $parent->setAttribute("class", "active");
                    }
                }
            }


            //Create a link element;
            $link = $Node->ownerDocument->createElement("li");
            $link->setAttribute("class", 'link-' . $class . " " . ((isset($item['menu_classes']) && !empty($item['menu_classes'])) ? $item['menu_classes'] : "") . (($active) ? " active " : ""));


            //Create the anchor and add to the link
            $anchor = $Node->ownerDocument->createElement("a");
            $anchor->setAttribute("href", $item["menu_url"]);
            $anchor->setAttribute("title", $item["menu_title"]);

            //Additional attributes;
            $anchorAttributes = isset($item['menu_attributes'])? $item['menu_attributes']  : [] ;

            if(!empty($anchorAttributes) && is_array($anchorAttributes)){
                foreach($anchorAttributes as $attribute => $value ){
                    $anchor->setAttribute( $attribute, $value );
                }
            }
            //Show menu Icons?
            if ($menuIcons) {
                //If we have a menu count
                $icon = $Node->ownerDocument->createElement("i");
                $icon->setAttribute("class", "menu-icon icon-{$class}");

                $anchor->appendChild($icon);

            }

            //create the anchor title;
            $title = $Node->ownerDocument->createElement("span");
            $title->setAttribute("class", "menu-text");
            $title->setAttribute("title", $item["menu_title"]);
            $title->appendChild($Node->ownerDocument->createTextNode($item["menu_title"]));

            //Append the title to the anchor
            $anchor->appendChild($title);


            //Show an unread count?
            if (isset($item['menu_count'])) {

                //If we have a menu count
                $important = (isset($item['menu_count_unimportant']) && (bool)$item['menu_count_unimportant'] || (int)$item['menu_count'] < 1) ? null : "label-important";

                $badge = $Node->ownerDocument->createElement("span");
                $badge->setAttribute("class", "badge " . ((!$menuIcons) ? " absolute-right" : "nav-icon-label") . " {$important}");
                $badge->appendChild($Node->ownerDocument->createTextNode(number_format($item['menu_count'])));


                $anchor->appendChild($badge);
            }


            //Count children
            if (isset($item['children']) && count($item['children']) > 0 && $depth < $menuDepth) {

                //Dropdown li

                $caret = $Node->ownerDocument->createElement("b");
                $caret->setAttribute("class", "caret");
                $anchor->appendChild($caret);

                //Add the menu anchor
                $dropup = $Node->hasAttribute("dropup")? " dropup ": null;

                $link->appendChild($anchor);
                $link->setAttribute("class",  'dropdown ' .$dropup. $link->getAttribute("class"));

                $parents[] = $link;

                $_list = $this->render($Node, (array)$item['children'], $menuType, $menuDepth, $menuIcons, $parents);


                $parents = [];

                //Dropdown ul
                $_list->setAttribute("class", 'nav menu dropdown-menu ' . $_list->getAttribute("class"));

                $link->appendChild($_list);
                $depth++;

            } else {
                //Just Add the menu anchor
                $link->appendChild($anchor);
            }
            $list->appendChild($link);
        }

        $list->setAttribute("class", $menuType);

        return $list;
    }
}