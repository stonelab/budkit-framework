<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 03/07/2014
 * Time: 11:23
 */

namespace Budkit\Application\Support;

use ReflectionClass;
use ReflectionException;

trait Mockery {

    protected static $mockableInterface = 'Budkit\Application\Support\Mockable';
    protected static $mockableTrait     = 'Budkit\Application\Support\Mock';


    public function setMockableInterface($interface) {
        static::$mockableInterface = $interface;
    }

    public function setMockableTrait($trait) {
        static::$mockableTrait = $trait;
    }

    public function createAliasMock($alias, $original = null, $autoload = true) {

        //If we are passing a large array;
        if (is_array($alias)) {
            foreach ($alias as $mock => $original) {
                $this->createAliasMock($mock, $original);
            }

            return;
        }

        //If the original class is mockable?
        try {
            $mockable = new ReflectionClass($original);
            if ($mockable->implementsInterface(static::$mockableInterface)) {

                //All mockable classes must use the MockTrait!
                $traits = $mockable->getTraits();

                if (!array_key_exists(static::$mockableTrait, $traits)) {
                    //@TODO throw an exception saying there is no mock;
                    return;
                }

                //The MockClass Name
                $class = $alias = ucfirst($alias);

                if ($mockable->isInstantiable()) {
                    if (!class_exists($class)) {

                        class_alias($original, $class);

                        //Define the original mocking container;
                        return $class::resolveOriginalClass($this, $original);
                    }
                }
            }
        }
        catch (ReflectionException $E) {
            //Do nothing; //maybe log to class saying not mockable;
        }
    }
} 