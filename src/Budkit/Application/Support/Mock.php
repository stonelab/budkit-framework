<?php

namespace Budkit\Application\Support;

use Budkit\Dependency\Container;

/**
 * Declares methods required by the Mockable Interface
 *
 * @See [Budkit\Application\Support\Mockable](?file=Budkit/Application/Support/Mockable.php)
 */
trait Mock
{

    private static $classContainer;

    private static $originalClass;


    /**
     * All Mockable class must register a container the name of the original class.
     *
     * If Mockable is managed by a different container, remember to call this method after the
     * object is instantiated.
     *
     * @param Container $container the current app instance container
     * @param           $original Mockable class name
     */
    public static function resolveOriginalClass(Container $container, $original)
    {

        static::$classContainer = $container;
        static::$originalClass = $original;

    }


    /**
     * Handle dynamic, static calls to the object.
     *
     * @param $method The method to call
     * @param $arguments Arguments to be passed to a Mockable class method
     * @return mixed Mockable class method output
     */
    public static function __callStatic($method, $arguments)
    {

        //print_r(static::$classContainer->createInstance(static::$originalClass));

        $instance = static::$classContainer->createInstance(static::$originalClass);

        return call_user_func_array([&$instance, $method], $arguments);
    }

} 