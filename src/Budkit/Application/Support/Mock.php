<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 03/07/2014
 * Time: 11:47
 */

namespace Budkit\Application\Support;

use Budkit\Dependency\Container;

trait Mock {

    private static $classContainer;
    private static $originalClass;


    /**
     * All mockable class must register a container the name of the original class;
     *
     * @param Container $container
     * @param           $original
     */
    public static function resolveOriginalClass(Container $container, $original) {

        static::$classContainer = $container;
        static::$originalClass  = $original;

    }


    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string $method
     * @param  array  $args
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments) {

        //print_r(static::$classContainer->createInstance(static::$originalClass));

        $instance = static::$classContainer->createInstance(static::$originalClass);

        return call_user_func_array([&$instance, $method], $arguments);
    }

} 