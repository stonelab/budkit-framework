<?php

namespace Budkit\Application\Support;
use Budkit\Dependency\Container;



/**
 * The Mockable class provides an interface for object Mocking.
 *
 * Mocking simulates the behavior of a real package objects in controlled ways.
 * Although class mocking should generally be avoided, Mock'ed objects are useful in
 * certain circumstances such as unit testing.
 *
 * Classes to be mock'd must implement this delegate. You may then use the [Budkit\Application\Support\Mock](#) trait
 * or declare the required abstract methods;
 *
 * Remember to call [::resolveOriginalClass](#method:resolveOriginalClass) in the class constructor
 * if the object is not created using the application container's [createAliasMock()](#)
 *
 * Note that only app containers using the [Budkit\Application\Support\Mockery](#) trait can create mock container objects
 *
 * *Usage:*
 *
 *     use Budkit\Application\Support\Mockable;
 *     use Budkit\Application\Support\Mock;
 *
 *     class View implements Mockable{
 *
 *          use Mock;
 *
 *          public __construct(Container $container){
 *               //If you are not instantiating thi
 *               static::resolveOriginalClass($container, __CLASS__);
 *          }
 *
 *          public function display(){
 *              echo "Displayed";
 *          }
 *     }
 *
 *
 *     //Mocking the class
 *     $app = new Budkit\Application\Instance();
 *     $app->createAliasMock(
 *          "display" => View::class
 *      );
 *
 *      //Run the display method in view
 *      $app->view->display() ; //outputs "Displayed"
 *
 *      //using the class mock
 *      Display::display(); //outputs "Displayed"
 *
 *
 * @package Budkit\Application\Support
 */
interface Mockable
{


    /**
     * All Mockable class must register a container the name of the original class.
     *
     * If Mockable is managed by a different container, remember to call this method after the
     * object is instantiated.
     *
     * @param Container $container the current app instance container
     * @param           $original Mockable class name
     */
    public static function resolveOriginalClass(Container $container, $original);


    /**
     * Handle dynamic, static calls to the object.
     *
     * @param $method The method to call
     * @param $arguments Arguments to be passed to a Mockable class method
     * @return mixed Mockable class method output
     */
    public static function __callStatic($method, $arguments);


} 